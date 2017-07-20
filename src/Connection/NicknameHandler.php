<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 20-7-2017
 * Time: 12:38
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Modules\ModuleInterface;
use Yoshi2889\Container\ComponentTrait;

class NicknameHandler implements ModuleInterface
{
	use ComponentTrait;
	use ContainerTrait;

	protected $nicknames = [];
	protected $tryNicknames = [];

	/**
	 * NicknameHandler constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		if (empty(Configuration::fromContainer($container)['alternativeNicknames']))
			return;

		$this->nicknames = Configuration::fromContainer($container)['alternativeNicknames'];

		// 001: RPL_WELCOME
		EventEmitter::fromContainer($container)->on('irc.line.in.001', [$this, 'deregisterListeners']);

		// 431: ERR_NONICKNAMEGIVEN
		// 432: ERR_ERRONEUSNICKNAME
		// 433: ERR_NICKNAMEINUSE
		// 436: ERR_NICKCOLLISION
		EventEmitter::fromContainer($container)->on('irc.line.in.431', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($container)->on('irc.line.in.432', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($container)->on('irc.line.in.433', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($container)->on('irc.line.in.436', [$this, 'chooseAlternateNickname']);
		$this->setContainer($container);
	}

	public function deregisterListeners()
	{
		EventEmitter::fromContainer($this->getContainer())->removeListener('irc.line.in.431', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($this->getContainer())->removeListener('irc.line.in.432', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($this->getContainer())->removeListener('irc.line.in.433', [$this, 'chooseAlternateNickname']);
		EventEmitter::fromContainer($this->getContainer())->removeListener('irc.line.in.436', [$this, 'chooseAlternateNickname']);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function chooseAlternateNickname(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		if (empty($this->tryNicknames))
			$this->tryNicknames = $this->nicknames;

		if (empty($this->nicknames))
		{
			Logger::fromContainer($this->getContainer())->warning('Out of nicknames to try; giving up.');
			IrcConnection::fromContainer($this->getContainer())->close();
			return;
		}

		$nickname = array_shift($this->nicknames);
		$queue->nick($nickname);
	}
}