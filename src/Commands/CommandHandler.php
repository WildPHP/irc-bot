<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use Collections\Dictionary;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\ComponentTrait;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Security\Validator;
use WildPHP\Core\Users\User;
use WildPHP\Core\Users\UserCollection;

class CommandHandler
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * @var Dictionary
	 */
	protected $commandDictionary = null;

	/**
	 * CommandHandler constructor.
	 *
	 * @param ComponentContainer $container
	 * @param Dictionary $commandDictionary
	 */
	public function __construct(ComponentContainer $container, Dictionary $commandDictionary)
	{
		$this->setCommandDictionary($commandDictionary);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);
		$this->setContainer($container);
	}

	/**
	 * @param string $command
	 * @param callable $callback
	 * @param CommandHelp|null $commandHelp
	 * @param int $minarguments
	 * @param int $maxarguments
	 * @param string $requiredPermission
	 *
	 * @return bool
	 */
	public function registerCommand(string $command,
	                                callable $callback,
	                                CommandHelp $commandHelp = null,
	                                int $minarguments = -1,
	                                int $maxarguments = -1,
	                                string $requiredPermission = '')
	{
		if ($this->getCommandDictionary()
			->keyExists($command)
		)
			return false;

		$commandObject = CommandFactory::create($callback, $commandHelp, $minarguments, $maxarguments, $requiredPermission);
		$this->getCommandDictionary()[$command] = $commandObject;

		return true;
	}

	/**
	 * @param string $originalCommand
	 * @param string $alias
	 *
	 * @return bool
	 */
	public function alias(string $originalCommand, string $alias): bool
	{
		if (!$this->getCommandDictionary()->keyExists($originalCommand) || $this->getCommandDictionary()->keyExists($alias))
			return false;

		$commandObject = $this->getCommandDictionary()[$originalCommand];
		$this->getCommandDictionary()[$alias] = $commandObject;
		return true;
	}

	/**
	 * @param PRIVMSG $privmsg
	 * @param Queue $queue
	 */
	public function parseAndRunCommand(PRIVMSG $privmsg, Queue $queue)
	{
		/** @var User $user */
		$user = UserCollection::fromContainer($this->getContainer())
			->findOrCreateByNickname($privmsg->getNickname());

		/** @var Channel $source */
		$source = ChannelCollection::fromContainer($this->getContainer())
			->requestByChannelName($privmsg->getChannel(), $user);
		$message = $privmsg->getMessage();

		$args = [];
		$command = self::parseCommandFromMessage($message, $args);

		if ($command === false)
			return;

		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.command', [$command, $source, $user, $args, $this->getContainer()]);

		$dictionary = $this->getCommandDictionary();

		if (!$dictionary->keyExists($command))
			return;

		$commandObject = $dictionary[$command];
		$permission = $commandObject->getRequiredPermission();
		if ($permission && !Validator::fromContainer($this->getContainer())
				->isAllowedTo($permission, $user, $source)
		)
		{
			$queue->privmsg($source->getName(),
				$user->getNickname() . ': You do not have the required permission to run this command (' . $permission . ')');

			return;
		}

		$maximumArguments = $commandObject->getMaximumArguments();
		if (count($args) < $commandObject->getMinimumArguments() || ($maximumArguments != -1 && count($args) > $maximumArguments))
		{
			$prefix = Configuration::fromContainer($this->getContainer())
				->get('prefix')
				->getValue();
			$queue->privmsg($source->getName(),
				'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $command . ' for usage instructions.');

			return;
		}

		call_user_func($commandObject->getCallback(), $source, $user, $args, $this->getContainer(), $command);
	}

	/**
	 * @param string $message
	 * @param array $args
	 *
	 * @return false|string
	 */
	protected function parseCommandFromMessage(string $message, array &$args)
	{
		$messageParts = explode(' ', trim($message));
		$firstPart = array_shift($messageParts);
		$prefix = Configuration::fromContainer($this->getContainer())
			->get('prefix')
			->getValue();

		if (strlen($firstPart) == strlen($prefix))
			return false;

		if (substr($firstPart, 0, strlen($prefix)) != $prefix)
			return false;

		$command = substr($firstPart, strlen($prefix));

		// Remove empty elements and excessive spaces.
		$args = array_values(array_map('trim', array_filter($messageParts)));

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
}