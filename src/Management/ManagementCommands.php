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

namespace WildPHP\Core\Management;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Commands\CommandHelp;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Users\User;

class ManagementCommands
{
	/**
	 * @var ComponentContainer
	 */
	protected $container;

	/**
	 * ManagementCommands constructor.
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Joins the specified channel(s).');
		$commandHelp->addPage('Usage: join [channel] ([channel]) ([channel]) ... (up to 5 channels)');
		$commandHelp->addPage('Required permission: join');
		$container->getCommandHandler()->registerCommand('join', [$this, 'joinCommand'], $commandHelp, 1, 5);

		$commandHelp = new CommandHelp();
		$commandHelp->addPage('Parts (leaves) the specified channel(s).');
		$commandHelp->addPage('Usage: part ([channel]) ([channel]) ([channel]) ... (up to 5 channels)');
		$commandHelp->addPage('Required permission: part');
		$container->getCommandHandler()->registerCommand('part', [$this, 'partCommand'], $commandHelp, 0, 5);
		$this->setContainer($container);
	}

	protected function validateChannels(array $channels): array
	{
		$validChannels = [];
		$serverChannelPrefix = $this->getContainer()->getConfiguration()->get('serverConfig.chantypes')->getValue();
		foreach ($channels as $channel)
		{
			if (substr($channel, 0, strlen($serverChannelPrefix)) != $serverChannelPrefix)
				continue;

			$validChannels[] = $channel;
		}

		return $validChannels;
	}

	public function joinCommand(Channel $source, User $user, $channels, ComponentContainer $container)
	{
		$result = $container->getValidator()->isAllowedTo('join', $user, $source);

		if (!$result)
		{
			$container->getQueue()->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to use the join command.');
			return;
		}

		$validChannels = $this->validateChannels($channels);

		$container->getQueue()->join($validChannels);

		$diff = array_diff($channels, $validChannels);

		if (!empty($diff))
			$container->getQueue()->privmsg($user->getNickname(), 'Did not join the following channels because they do not follow proper formatting: ' . implode(', ', $diff));
	}

	public function partCommand(Channel $source, User $user, $channels, ComponentContainer $container)
	{
		$result = $container->getValidator()->isAllowedTo('part', $user, $source);

		if (!$result)
		{
			$container->getQueue()->privmsg($source->getName(), $user->getNickname() . ': You are not allowed to use the part command.');
			return;
		}

		if (empty($channels))
			$channels = [$source->getName()];

		$validChannels = $this->validateChannels($channels);

		$container->getQueue()->part($validChannels);

		$diff = array_diff($channels, $validChannels);

		if (!empty($diff))
			$container->getQueue()->privmsg($user->getNickname(), 'Did not part the following channels because they do not follow proper formatting: ' . implode(', ', $diff));
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer($container)
	{
		$this->container = $container;
	}


}