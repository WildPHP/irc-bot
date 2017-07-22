<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;

use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
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
	                                ?CommandHelp $commandHelp = null,
	                                int $minarguments = -1,
	                                int $maxarguments = -1,
	                                string $requiredPermission = '')
	{
		if ($this->getCommandCollection()
			->offsetExists($command)
		)
			return false;

		$commandObject = CommandFactory::create($callback, $commandHelp, $minarguments, $maxarguments, $requiredPermission);
		$this->getCommandCollection()->offsetSet($command, $commandObject);

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
		if (!$this->getCommandCollection()->offsetExists($originalCommand) || $this->getCommandCollection()->offsetExists($alias))
			return false;

		$commandObject = $this->getCommandCollection()[$originalCommand];
		$this->getCommandCollection()->offsetSet($alias, $commandObject);
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

		if (!$source)
			return;

		$user = $source->getUserCollection()->findByNickname($privmsg->getNickname());

		if (!$user)
			return;

		$message = $privmsg->getMessage();

		$args = [];
		$command = self::parseCommandFromMessage($message, $args);

		if ($command === false)
			return;

		EventEmitter::fromContainer($this->getContainer())
			->emit('irc.command', [$command, $source, $user, $args, $this->getContainer()]);

		$dictionary = $this->getCommandCollection();

		if (!$dictionary->offsetExists($command))
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
			$prefix = Configuration::fromContainer($this->getContainer())['prefix'];
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