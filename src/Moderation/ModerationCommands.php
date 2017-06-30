<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Moderation;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Tasks\Task;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Users\User;

class ModerationCommands
{
	/**
	 * ModerationCommands constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Kicks the specified user from the channel. Usage: kick [nickname] ([reason])');
		CommandHandler::fromContainer($container)
			->registerCommand('kick', [$this, 'kickCommand'], $commandHelp, 1, -1, 'kick');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Requests a user to leave the channel. Usage: remove [nickname] ([reason])');
		CommandHandler::fromContainer($container)
			->registerCommand('remove', [$this, 'removeCommand'], $commandHelp, 1, -1, 'remove');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Changes the topic for the specified channel. Usage: topic ([channel]) [message]');
		CommandHandler::fromContainer($container)
			->registerCommand('topic', [$this, 'topicCommand'], $commandHelp, 1, -1, 'topic');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Kicks the specified user from the channel and adds a ban. Usage #1: kban [nickname] [minutes] ([reason])');
		$commandHelp->addPage('Usage #2: kban [nickname] [minutes] [redirect channel] ([reason])');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandHandler::fromContainer($container)
			->registerCommand('kban', [$this, 'kbanCommand'], $commandHelp, 2, -1, 'kban');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Bans the specified user from the channel. Usage #1: ban [nickname] [minutes]');
		$commandHelp->addPage('Usage #2: ban [nickname] [minutes] [redirect channel]');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandHandler::fromContainer($container)
			->registerCommand('ban', [$this, 'banCommand'], $commandHelp, 2, 3, 'ban');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Bans the specified host from the channel. Usage #1: banhost [hostname] [minutes]');
		$commandHelp->addPage('Usage #2: banhost [hostname] [minutes] [redirect channel]');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandHandler::fromContainer($container)
			->registerCommand('banhost', [$this, 'banhostCommand'], $commandHelp, 2, 3, 'ban');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Changes mode for a specified user, or channel if no user given. Usage: mode [modes] ([nickname])');
		CommandHandler::fromContainer($container)
			->registerCommand('mode', [$this, 'modeCommand'], $commandHelp, 1, 2, 'mode');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function kickCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$nickname = array_shift($args);
		$message = !empty($args) ? implode(' ', $args) : $nickname;
		$userObj = $source->getUserCollection()
			->findByNickname($nickname);

		if ($nickname == Configuration::fromContainer($container)['currentNickname'])
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'I refuse to hurt myself!');

			return;
		}

		if (!$userObj)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');

			return;
		}

		Queue::fromContainer($container)
			->kick($source->getName(), $nickname, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function removeCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$nickname = array_shift($args);
		$message = !empty($args) ? implode(' ', $args) : $nickname;
		$userObj = $source->getUserCollection()
			->findByNickname($nickname);

		if ($nickname == Configuration::fromContainer($container)['currentNickname'])
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'What? I can\'t leave...!');

			return;
		}

		if (!$userObj)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');

			return;
		}

		Queue::fromContainer($container)
			->remove($source->getName(), $nickname, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function topicCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$channelName = $source->getName();

		if (Channel::isValidName($args[0], Configuration::fromContainer($container)['prefix']))
			$channelName = array_shift($args);

		$message = implode(' ', $args);

		Queue::fromContainer($container)
			->topic($channelName, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function kbanCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$nickname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0], Configuration::fromContainer($container)['prefix']) ? array_shift($args) : '';
		$message = !empty($args) ? implode(' ', $args) : $nickname;
		$userObj = $source->getUserCollection()
			->findByNickname($nickname);

		if ($nickname == Configuration::fromContainer($container)['currentNickname'])
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'I refuse to hurt myself!');

			return;
		}

		if (!$userObj)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');

			return;
		}

		$time = time() + 60 * $minutes;
		$this->banUser($source, $userObj, $container, $time, $redirect);

		Queue::fromContainer($container)
			->kick($source->getName(), $nickname, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function banCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$nickname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0], Configuration::fromContainer($container)['prefix']) ? array_shift($args) : '';
		$userObj = $source->getUserCollection()
			->findByNickname($nickname);

		if ($nickname == Configuration::fromContainer($container)['currentNickname'])
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), 'I refuse to hurt myself!');

			return;
		}

		if (!$userObj)
		{
			Queue::fromContainer($container)
				->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');

			return;
		}

		$time = time() + 60 * $minutes;
		$this->banUser($source, $userObj, $container, $time, $redirect);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function banhostCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$hostname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0], Configuration::fromContainer($container)['prefix']) ? array_shift($args) : '';
		$time = time() + 60 * $minutes;
		$this->banUser($source, $hostname, $container, $time, $redirect);

		if (!empty($redirect))
			$hostname .= '$' . $redirect;

		if ($time != 0)
		{
			$args = [$source, $hostname, $container];
			$task = new Task([$this, 'removeBan'], $time, $args);
			TaskController::fromContainer($container)
				->addTask($task);
		}

		Queue::fromContainer($container)
			->mode($source->getName(), '+b', [$hostname]);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function modeCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$modes = array_shift($args);
		$nickname = array_shift($args);

		$target = [];
		if (!empty($nickname))
		{
			$userObj = $source->getUserCollection()
				->findByNickname($nickname);

			if (!$userObj)
			{
				Queue::fromContainer($container)
					->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');

				return;
			}
			$target = [$nickname];
		}

		Queue::fromContainer($container)
			->mode($source->getName(), $modes, $target);
	}

	/**
	 * @param Channel $source
	 * @param User $userObj
	 * @param ComponentContainer $container
	 * @param int $until
	 * @param string $redirect
	 */
	protected function banUser(Channel $source, User $userObj, ComponentContainer $container, int $until, string $redirect = '')
	{
		$hostname = $userObj->getHostname();
		$username = $userObj->getUsername();
		$ban = '*!' . $username . '@' . $hostname;

		if (!empty($redirect))
			$ban .= '$' . $redirect;

		if ($until != 0)
		{
			$args = [$source, $ban, $container];
			$task = new Task([$this, 'removeBan'], $until, $args);
			TaskController::fromContainer($container)
				->addTask($task);
		}

		Queue::fromContainer($container)
			->mode($source->getName(), '+b', [$ban]);
	}

	/**
	 * @param Task $task
	 * @param Channel $source
	 * @param string $banmask
	 * @param ComponentContainer $container
	 */
	public function removeBan(Task $task, Channel $source, string $banmask, ComponentContainer $container)
	{
		Queue::fromContainer($container)
			->mode($source->getName(), '-b', [$banmask]);
	}
}