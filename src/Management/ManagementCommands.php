<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Management;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandHandler;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;

use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class ManagementCommands
{
	use ContainerTrait;

	/**
	 * ManagementCommands constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Joins the specified channel(s). Usage: join [channel] ([channel]) ([channel]) ... (up to 5 channels)');
		CommandHandler::fromContainer($container)
			->registerCommand('join', [$this, 'joinCommand'], $commandHelp, 1, 5, 'join');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Parts (leaves) the specified channel(s). Usage: part ([channel]) ([channel]) ([channel]) ... (up to 5 channels)');
		CommandHandler::fromContainer($container)
			->registerCommand('part', [$this, 'partCommand'], $commandHelp, 0, 5, 'part');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Quits the IRC network. Usage: quit ([message])');
		CommandHandler::fromContainer($container)
			->registerCommand('quit', [$this, 'quitCommand'], $commandHelp, 0, -1, 'quit');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Shows info about a user. Usage: whois [nickname]');
		CommandHandler::fromContainer($container)
			->registerCommand('whois', [$this, 'whoisCommand'], $commandHelp, 1, 1, 'whois');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Changes the nickname of the bot. Usage: nick [nickname]');
		CommandHandler::fromContainer($container)
			->registerCommand('nick', [$this, 'nickCommand'], $commandHelp, 0, 1, 'nick');

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Clears the send queue.');
		CommandHandler::fromContainer($container)
			->registerCommand('clearqueue', [$this, 'clearQueueCommand'], $commandHelp, 0, 1, 'clearqueue');

		$this->setContainer($container);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $args
	 * @param ComponentContainer $container
	 */
	public function quitCommand(Channel $source, User $user, $args, ComponentContainer $container)
	{
		$message = implode(' ', $args);

		if (empty($message))
			$message = 'Quit command given by ' . $user->getNickname();

		Queue::fromContainer($container)
			->quit($message);
	}

	/**
	 * @param array $channels
	 *
	 * @return array
	 */
	protected function validateChannels(array $channels): array
	{
		$validChannels = [];
		$serverChannelPrefix = Configuration::fromContainer($this->getContainer())
			->get('serverConfig.chantypes')
			->getValue();
		foreach ($channels as $channel)
		{
			if (substr($channel, 0, strlen($serverChannelPrefix)) != $serverChannelPrefix)
				continue;

			$validChannels[] = $channel;
		}

		return $validChannels;
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $channels
	 * @param ComponentContainer $container
	 */
	public function joinCommand(Channel $source, User $user, $channels, ComponentContainer $container)
	{
		$validChannels = $this->validateChannels($channels);

		Queue::fromContainer($container)
			->join($validChannels);

		$diff = array_diff($channels, $validChannels);

		if (!empty($diff))
			Queue::fromContainer($container)
				->privmsg($user->getNickname(),
					'Did not join the following channels because they do not follow proper formatting: ' . implode(', ', $diff));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param $channels
	 * @param ComponentContainer $container
	 */
	public function partCommand(Channel $source, User $user, $channels, ComponentContainer $container)
	{
		if (empty($channels))
			$channels = [$source->getName()];

		$validChannels = $this->validateChannels($channels);

		Queue::fromContainer($container)
			->part($validChannels);

		$diff = array_diff($channels, $validChannels);

		if (!empty($diff))
			Queue::fromContainer($container)
				->privmsg($user->getNickname(),
					'Did not part the following channels because they do not follow proper formatting: ' . implode(', ', $diff));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param array $args
	 * @param ComponentContainer $container
	 */
	public function whoisCommand(Channel $source, User $user, array $args, ComponentContainer $container)
	{
		$wantedNickname = $args[0];
		/** @var User $userObject */
		$userObject = UserCollection::fromContainer($container)->findByNickname($wantedNickname);

		if (!$userObject)
		{
			Queue::fromContainer($container)->privmsg($source->getName(), $user->getNickname() . ': This user is not online.');
			return;
		}

		Queue::fromContainer($container)->privmsg($source->getName(), $user->getNickname() . ': I am sending you the data in private.');

		$hostname = $userObject->getHostname();
		$username = $userObject->getUsername();

		/** @var Channel[] $channels */
		$channels = $userObject->getChannelCollection()->getArrayCopy();
		foreach ($channels as $key => $channel)
		{
			$channels[$key] = $channel->getName();
		}

		Queue::fromContainer($container)->privmsg($user->getNickname(), $wantedNickname . ': username: ' . $username);
		Queue::fromContainer($container)->privmsg($user->getNickname(), $wantedNickname . ': hostname: ' . $hostname);
		Queue::fromContainer($container)->privmsg($user->getNickname(), $wantedNickname . ' is on channels ' . implode(', ', $channels));
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param array $args
	 * @param ComponentContainer $container
	 */
	public function nickCommand(Channel $source, User $user, array $args, ComponentContainer $container)
	{
		// TODO: Validate
		Queue::fromContainer($container)->nick($args[0]);
	}

	/**
	 * @param Channel $source
	 * @param User $user
	 * @param array $args
	 * @param ComponentContainer $container
	 */
	public function clearQueueCommand(Channel $source, User $user, array $args, ComponentContainer $container)
	{
		Queue::fromContainer($container)->clear();
		Queue::fromContainer($container)->privmsg($source->getName(), $user->getNickname() . ': Message queue cleared.');
	}
}