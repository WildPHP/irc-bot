<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Moderation;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\Commands\CommandRegistrar;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Tasks\Task;
use WildPHP\Core\Tasks\TaskController;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ModerationCommands
{
	public function __construct()
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Kicks the specified user from the channel.');
		$commandHelp->addPage('Usage: kick [nickname] ([reason])');
		CommandRegistrar::registerCommand('kick', [$this, 'kickCommand'], $commandHelp, 1, -1, 'kick');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Changes the topic for the specified channel.');
		$commandHelp->addPage('Usage: topic ([channel]) [message]');
		CommandRegistrar::registerCommand('topic', [$this, 'topicCommand'], $commandHelp, 1, -1, 'topic');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Kicks the specified user from the channel and adds a ban.');
		$commandHelp->addPage('Usage #1: kban [nickname] [minutes] ([reason])');
		$commandHelp->addPage('Usage #2: kban [nickname] [minutes] [redirect channel] ([reason])');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandRegistrar::registerCommand('kban', [$this, 'kbanCommand'], $commandHelp, 2, -1, 'kban');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Bans the specified user from the channel.');
		$commandHelp->addPage('Usage #1: ban [nickname] [minutes]');
		$commandHelp->addPage('Usage #2: ban [nickname] [minutes] [redirect channel]');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandRegistrar::registerCommand('ban', [$this, 'banCommand'], $commandHelp, 2, 3, 'ban');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Bans the specified host from the channel.');
		$commandHelp->addPage('Usage #1: ban [hostname [minutes]');
		$commandHelp->addPage('Usage #2: ban [hostname] [minutes] [redirect channel]');
		$commandHelp->addPage('Pass 0 minutes for an indefinite ban.');
		CommandRegistrar::registerCommand('banhost', [$this, 'banhostCommand'], $commandHelp, 2, 3, 'ban');


		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Changes mode for a specified user.');
		$commandHelp->addPage('Usage: mode [nickname] [modes]');
		CommandRegistrar::registerCommand('mode', [$this, 'modeCommand'], $commandHelp, 2, -1, 'mode');
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function kickCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$nickname = array_shift($args);
		$message = !empty($args) ? implode(' ', $args) : $nickname;
		$userObj = $source->getUserCollection()->findByNickname($nickname);

		if ($nickname == Configuration::get('currentNickname')->getValue())
		{
			$queue->privmsg($source->getName(), 'I refuse to hurt myself!');
			return;
		}

		if (!$userObj)
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');
			return;
		}

		$queue->kick($source->getName(), $nickname, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function topicCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$channelName = $source->getName();

		if (Channel::isValidName($args[0]))
			$channelName = array_shift($args);

		$message = implode(' ', $args);

		$queue->topic($channelName, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function kbanCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$nickname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0]) ? array_shift($args) : '';
		$message = !empty($args) ? implode(' ', $args) : $nickname;
		$userObj = $source->getUserCollection()->findByNickname($nickname);

		if ($nickname == Configuration::get('currentNickname')->getValue())
		{
			$queue->privmsg($source->getName(), 'I refuse to hurt myself!');
			return;
		}

		if (!$userObj)
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');
			return;
		}

		$time = time() + 60 * $minutes;
		$this->banUser($source, $userObj, $queue, $time, $redirect);

		$queue->kick($source->getName(), $nickname, $message);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function banCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$nickname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0]) ? array_shift($args) : '';
		$userObj = $source->getUserCollection()->findByNickname($nickname);

		if ($nickname == Configuration::get('currentNickname')->getValue())
		{
			$queue->privmsg($source->getName(), 'I refuse to hurt myself!');
			return;
		}

		if (!$userObj)
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');
			return;
		}

		$time = time() + 60 * $minutes;
		$this->banUser($source, $userObj, $queue, $time, $redirect);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function banhostCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$hostname = array_shift($args);
		$minutes = array_shift($args);
		$redirect = !empty($args) && Channel::isValidName($args[0]) ? array_shift($args) : '';
		$time = time() + 60 * $minutes;
		$this->banUser($source, $hostname, $queue, $time, $redirect);

		if (!empty($redirect))
			$hostname .= '$' . $redirect;

		if ($time != 0)
		{
			$args = [$source, $hostname, $queue];
			$task = new Task([$this, 'removeBan'], $time, $args);
			TaskController::addTask($task);
		}

		$queue->mode($source->getName(), '+b', $hostname);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param Queue $queue
	 */
	public function modeCommand(Channel $source, User $user, $args, Queue $queue)
	{
		$nickname = array_shift($args);
		$modes = array_shift($args);
		$userObj = $source->getUserCollection()->findByNickname($nickname);

		if (!$userObj)
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': This user is currently not in the channel.');
			return;
		}

		$queue->mode($source->getName(), $modes, $nickname);
	}

	/**
	 * @param Channel $source
	 * @param User $userObj
	 * @param Queue $queue
	 * @param int $until
	 * @param string $redirect
	 */
	protected function banUser(Channel $source, User $userObj, Queue $queue, int $until, string $redirect = '')
	{
		$hostname = $userObj->getHostname();
		$username = $userObj->getUsername();
		$ban = '*!' . $username . '@' . $hostname;

		if (!empty($redirect))
			$ban .= '$' . $redirect;

		if ($until != 0)
		{
			$args = [$source, $ban, $queue];
			$task = new Task([$this, 'removeBan'], $until, $args);
			TaskController::addTask($task);
		}

		$queue->mode($source->getName(), '+b', $ban);
	}

	/**
	 * @param Task $task
	 * @param Channel $source
	 * @param string $banmask
	 * @param Queue $queue
	 */
	public function removeBan(Task $task, Channel $source, string $banmask, Queue $queue)
	{
		$queue->mode($source->getName(), '-b', $banmask);
	}
}