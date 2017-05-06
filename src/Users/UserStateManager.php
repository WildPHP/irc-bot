<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 26-4-2017
 * Time: 16:17
 */

namespace WildPHP\Core\Users;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\IRCMessages\MODE;
use WildPHP\Core\Connection\IRCMessages\NICK;
use WildPHP\Core\Connection\IRCMessages\QUIT;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;

class UserStateManager
{
	use ContainerTrait;

	/**
	 * UserStateManager constructor.
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$events = [
			'irc.cap.ls' => 'requestChghost',

			// 366: RPL_ENDOFNAMES
			'irc.line.in.366' => 'sendInitialWhoxMessage',
			// 354: RPL_WHOSPCRPL
			'irc.line.in.354' => 'processWhoxReply',
			'irc.line.in.quit' => 'processUserQuit',
			'irc.line.in.nick' => 'processUserNicknameChange',
			'irc.line.in.mode' => 'processUserModeChange',

			// Requires the chghost extension. Freenode doesn't have it.
			'irc.line.in.chghost' => 'processUserHostnameChange',
		];

		foreach ($events as $event => $callback)
		{
			EventEmitter::fromContainer($container)
				->on($event, [$this, $callback]);
		}

		$this->setContainer($container);
	}

	public function requestChghost()
	{
		CapabilityHandler::fromContainer($this->getContainer())
			->requestCapability('chghost');
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function sendInitialWhoxMessage(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$channel = $ircMessage->getArgs()[1];
		$queue->who($channel, '%nuhaf');
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processWhoxReply(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$username = $args[1];
		$hostname = $args[2];
		$nickname = $args[3];
		$accountname = $args[5];
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findOrCreateByNickname($nickname);

		$userObject->setUsername($username);
		$userObject->setHostname($hostname);
		$userObject->setIrcAccount($accountname);

		Logger::fromContainer($this->getContainer())->debug('Updated user details', [
			'nickname' => $userObject->getNickname(),
			'username' => $username,
			'hostname' => $hostname,
			'accountname' => $accountname
		]);

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.account.changed', [$userObject, $queue]);
	}

	/**
	 * @param QUIT $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function processUserQuit(QUIT $incomingIrcMessage, Queue $queue)
	{
		$nickname = $incomingIrcMessage->getNickname();

		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($nickname);

		if ($userObject == false)
			return;

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.quit', [$userObject, $queue]);
		UserCollection::fromContainer($this->getContainer())
			->remove(function (User $user) use ($userObject)
			{
				return $user === $userObject;
			});
	}

	/**
	 * @param NICK $incomingIrcMessage
	 * @param Queue $queue
	 */
	public function processUserNicknameChange(NICK $incomingIrcMessage, Queue $queue)
	{
		$oldNickname = $incomingIrcMessage->getNickname();
		$newNickname = $incomingIrcMessage->getNewNickname();

		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($oldNickname);

		if ($userObject == false)
			return;

		$userObject->setNickname($newNickname);
		EventEmitter::fromContainer($this->getContainer())
			->emit('user.nick', [$userObject, $oldNickname, $newNickname, $queue]);
	}

	/**
	 * @param MODE $ircMessage
	 * @param Queue $queue
	 */
	public function processUserModeChange(MODE $ircMessage, Queue $queue)
	{
		$mode = $ircMessage->getFlags();
		$target = $ircMessage->getArguments()[0] ?? '';
		$channel = $ircMessage->getTarget();

		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($target);

		if ($userObject == false)
			return;

		if (!empty($channel))
			EventEmitter::fromContainer($this->getContainer())
				->emit('user.mode.channel', [$channel, $mode, $userObject, $queue]);
		else
			EventEmitter::fromContainer($this->getContainer())
				->emit('user.mode', [$mode, $userObject, $queue]);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processUserHostnameChange(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$newUsername = $args[0];
		$newHostname = $args[1];
		$userPrefix = UserPrefix::fromIncomingIrcMessage($ircMessage);
		$userObject = UserCollection::fromContainer($this->getContainer())
			->findByNickname($userPrefix->getNickname());

		$userObject->setHostname($newHostname);
		$userObject->setUsername($newUsername);

		EventEmitter::fromContainer($this->getContainer())
			->emit('user.host', [$userObject, $newUsername, $newHostname, $queue]);
	}
}