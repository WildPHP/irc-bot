<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

use ValidationClosures\Types;
use ValidationClosures\Utils;
use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Permissions\Validator;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class CommandHandler implements ComponentInterface
{
	use ComponentTrait;
	use ContainerTrait;

	/**
	 * @var Collection
	 */
	protected $commandCollection = null;

	/**
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * CommandHandler constructor.
	 *
	 * @param ComponentContainer $container
	 * @param Collection $commandCollection
	 */
	public function __construct(ComponentContainer $container, Collection $commandCollection)
	{
		$this->setCommandCollection($commandCollection);

		EventEmitter::fromContainer($container)
			->on('irc.line.in.privmsg', [$this, 'parseAndRunCommand']);
		$this->setContainer($container);
	}

	/**
	 * @param string $command
	 * @param Command $commandObject
	 * @param string[] $aliases
	 *
	 * @return bool
	 */
	public function registerCommand(string $command, Command $commandObject, array $aliases = [])
	{
		if ($this->getCommandCollection()->offsetExists($command) || !Utils::validateArray(Types::string(), $aliases))
			return false;

		$this->getCommandCollection()->offsetSet($command, $commandObject);
		
		Logger::fromContainer($this->getContainer())
			->debug(
				'New command registered', 
				['command' => $command]
			);
		
		foreach ($aliases as $alias)
			$this->alias($command, $alias);

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
		if (!$this->getCommandCollection()->offsetExists($originalCommand) || array_key_exists($alias, $this->aliases))
			return false;

		/** @var Command $commandObject */
		$commandObject = $this->getCommandCollection()[$originalCommand];
		$this->aliases[$alias] = $commandObject;
		return true;
	}

	/**
	 * @param PRIVMSG $privmsg
	 * @param Queue $queue
	 */
	public function parseAndRunCommand(PRIVMSG $privmsg, Queue $queue)
	{
		/** @var Channel $source */
		$ownNickname = Configuration::fromContainer($this->getContainer())['currentNickname'];
		$channel = $privmsg->getChannel() == $ownNickname ? $privmsg->getNickname() : $privmsg->getChannel();
		$source = ChannelCollection::fromContainer($this->getContainer())
			->findByChannelName($channel);

		$user = $source ? $source->getUserCollection()->findByNickname($privmsg->getNickname()) : false;

		if (!$user || !$source)
		{
			Logger::fromContainer($this->getContainer())
				->warning('!!! State mismatch!',
					[
						'user' => $privmsg->getNickname(),
						'channel' => $channel
					]
				);

			return;
		}

		$message = $privmsg->getMessage();

		$args = [];
		$command = $this->parseCommandFromMessage($message, $args);

		if ($command === false)
			return;

		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.command', [$command, $source, $user, $args, $this->getContainer()]);

		$commandObject = $this->findCommandInDictionary($command);

		if (!$commandObject)
			return;
		
		$permission = $commandObject->getRequiredPermission();
		if ($permission && !Validator::fromContainer($this->getContainer())->isAllowedTo($permission, $user, $source))
		{
			$queue->privmsg($source->getName(),
				$user->getNickname() . ': You do not have the required permission to run this command (' . $permission . ')');

			return;
		}

		$strategy = $this->findApplicableStrategy($commandObject, $args);
		
		if (!$strategy)
		{
			Logger::fromContainer($this->getContainer())->debug('No valid strategies found.');
			$prefix = Configuration::fromContainer($this->getContainer())['prefix'];
			$queue->privmsg($source->getName(),
				'Invalid arguments. Please check ' . $prefix . 'cmdhelp ' . $command . ' for usage instructions and make sure that your ' .
				'parameters match the given requirements.');
			
			return;
		}
		
		call_user_func($commandObject->getCallback(), $source, $user, $args, $this->getContainer(), $command);
	}

	/**
	 * @param string $command
	 *
	 * @return Command|null
	 */
	protected function findCommandInDictionary(string $command): ?Command
	{
		$dictionary = $this->getCommandCollection();

		if (!$dictionary->offsetExists($command) && !array_key_exists($command, $this->aliases))
			return null;

		/** @var Command $commandObject */
		return $dictionary[$command] ?? $this->aliases[$command];
	}

	/**
	 * @param Command $commandObject
	 * @param array $args
	 *
	 * @return null|ParameterStrategy
	 */
	protected function findApplicableStrategy(Command $commandObject, array &$args): ?ParameterStrategy
	{
		$parameterStrategies = $commandObject->getParameterStrategies();
		$strategy = null;
		$originalArgs = $args;

		/** @var ParameterStrategy $parameterStrategy */
		foreach ($parameterStrategies as $parameterStrategy)
		{
			try
			{
				$args = $parameterStrategy->validateArgumentArray($originalArgs);
				$strategy = $parameterStrategy;
				break;
			}
			catch (\InvalidArgumentException $e)
			{
				Logger::fromContainer($this->getContainer())->debug('Not applying strategy; ' . $e->getMessage());
			}
		}

		return $strategy;
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
		$prefix = Configuration::fromContainer($this->getContainer())['prefix'];

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
	 * @return Collection
	 */
	public function getCommandCollection(): Collection
	{
		return $this->commandCollection;
	}

	/**
	 * @param Collection $commandCollection
	 */
	public function setCommandCollection(Collection $commandCollection)
	{
		$this->commandCollection = $commandCollection;
	}
}