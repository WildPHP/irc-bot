<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\PRIVMSG;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\ModuleInterface;
use Yoshi2889\Container\ComponentTrait;

class MessageLogger implements ModuleInterface
{
	use ContainerTrait;
	use ComponentTrait;

    /**
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \Yoshi2889\Container\NotFoundException
     */
	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)
			->on('irc.line.in.privmsg', [$this, 'logIncomingPrivmsg']);

		EventEmitter::fromContainer($container)
			->on('irc.line.out', [$this, 'logOutgoingPrivmsg']);

		$this->setContainer($container);
	}

    /**
     * @param PRIVMSG $incoming
     * @throws \Yoshi2889\Container\NotFoundException
     */
	public function logIncomingPrivmsg(PRIVMSG $incoming)
	{
		$nickname = $incoming->getNickname();
		$channel = $incoming->getChannel();
		$message = $incoming->getMessage();

		$toLog = 'INC: [' . $channel . '] <' . $nickname . '> ' . $message;

		Logger::fromContainer($this->getContainer())
			->info($toLog);
	}

    /**
     * @param QueueItem $message
     * @param ComponentContainer $container
     * @throws \Yoshi2889\Container\NotFoundException
     */
	public function logOutgoingPrivmsg(QueueItem $message, ComponentContainer $container)
	{
		$command = $message->getCommandObject();

		if (!($command instanceof PRIVMSG))
			return;

		$channel = $command->getChannel();
		$msg = $command->getMessage();

		$toLog = 'OUT: [' . $channel . '] ' . $msg;
		Logger::fromContainer($container)
			->info($toLog);
	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}