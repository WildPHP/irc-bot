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

namespace WildPHP\Core\Commands;


use Collections\Dictionary;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IncomingIrcMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Security\Validator;

class CommandHandler
{
	use ComponentTrait;

	/**
	 * @var Dictionary
	 */
	protected $commandDictionary = null;

	/**
	 * @var ComponentContainer
	 */
	protected $container = null;

	public function __construct(ComponentContainer $container, Dictionary $commandDictionary)
	{
		$this->setCommandDictionary($commandDictionary);

		EventEmitter::fromContainer($container)->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);
		$this->setContainer($container);
	}

	/**
	 * @param string $command
	 * @param callable $callback
	 * @param CommandHelp|null $commandHelp
	 * @param int $minarguments
	 * @param int $maxarguments
	 * @param string $requiredPermission
	 * @return bool
	 */
	public function registerCommand(string $command, callable $callback, CommandHelp $commandHelp = null, int $minarguments = -1, int $maxarguments = -1, string $requiredPermission = '')
	{
		if ($this->getCommandDictionary()->keyExists($command))
			return false;

		$commandObject = CommandFactory::create($callback, $commandHelp, $minarguments, $maxarguments, $requiredPermission);
		$this->getCommandDictionary()[$command] = $commandObject;
		return true;
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function parseAndRunCommand(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$privmsg = PRIVMSG::fromIncomingIrcMessage($incomingIrcMessage);
		$source = $privmsg->getChannel();
		$message = $privmsg->getMessage();
		$user = $privmsg->getUser();

		$args = [];
		$command = self::parseCommandFromMessage($message, $args);

		if ($command === false)
			return;

		EventEmitter::fromContainer($this->getContainer())->emit('irc.command', [$command, $source, $user, $args, $this->getContainer()]);

		$dictionary = $this->getCommandDictionary();

		if (!$dictionary->keyExists($command))
			return;

		$commandObject = $dictionary[$command];
		$permission = $commandObject->getRequiredPermission();
		if ($permission && !Validator::fromContainer($this->getContainer())->isAllowedTo($permission, $user, $source))
		{
			$queue->privmsg($source->getName(), $user->getNickname() . ': You do not have the required permission to run this command (' . $permission . ')');
			return;
		}

		$maximumArguments = $commandObject->getMaximumArguments();
		if (count($args) < $commandObject->getMinimumArguments() || ($maximumArguments != -1 && count($args) > $maximumArguments))
		{
			$prefix = Configuration::fromContainer($this->getContainer())->get('prefix')->getValue();
			$queue->privmsg($source->getName(), 'Invalid arguments. Please check ' . $prefix . 'help ' . $command . ' for usage instructions.');
			return;
		}

		call_user_func($commandObject->getCallback(), $source, $user, $args, $this->getContainer());
	}

	/**
	 * @param string $message
	 * @param array $args
	 *
	 * @return false|string
	 */
	protected function parseCommandFromMessage(string $message, array &$args)
	{
		$messageParts = explode(' ', $message);
		$firstPart = $messageParts[0];
		$prefix = Configuration::fromContainer($this->getContainer())->get('prefix')->getValue();

		if (strlen($firstPart) == strlen($prefix))
			return false;

		if (substr($firstPart, 0, strlen($prefix)) != $prefix)
			return false;

		$command = substr($firstPart, strlen($prefix));
		array_shift($messageParts);
		$args = $messageParts;

		return $command;
	}

	/**
	 * @return Dictionary
	 */
	public function getCommandDictionary(): Dictionary
	{
		return $this->commandDictionary;
	}

	/**
	 * @param Dictionary $commandDictionary
	 */
	public function setCommandDictionary(Dictionary $commandDictionary)
	{
		$this->commandDictionary = $commandDictionary;
	}

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $componentContainer
	 */
	public function setContainer(ComponentContainer $componentContainer)
	{
		$this->container = $componentContainer;
	}
}