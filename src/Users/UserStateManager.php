<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 26-4-2017
 * Time: 16:17
 */

namespace WildPHP\Core\Users;


use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\Events\EventEmitter;

class UserStateManager
{
	public function __construct()
	{
		$events = [
			// 366: RPL_ENDOFNAMES
			'irc.line.in.366' => 'sendInitialWhoxMessage',
			// 354: RPL_WHOSPCRPL
			'irc.line.in.354' => 'processWhoxReply',
			'irc.line.in.quit' => 'processUserQuit',
			'irc.line.in.nick' => 'processUserNicknameChange',
			'irc.line.in.mode' => 'processUserModeChange',

			// Requiers the chghost extension. Freenode doesn't have it.
			'irc.line.in.chghost' => 'processUserHostnameChange',
		];

		foreach ($events as $event => $callback)
		{
			EventEmitter::on($event, [$this, $callback]);
		}

		CapabilityHandler::requestCapability('chghost');
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
		$userObject = UserCollection::globalFindOrCreateByNickname($nickname);

		$userObject->setUsername($username);
		$userObject->setHostname($hostname);
		$userObject->setIrcAccount($accountname);

		EventEmitter::emit('user.account.changed', [$userObject, $queue]);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processUserQuit(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];

		$userObject = UserCollection::getGlobalInstance()->findByNickname($nickname);

		if ($userObject == false)
			return;

		EventEmitter::emit('user.quit', [$userObject, $queue]);
		UserCollection::getGlobalInstance()->remove(function (User $user) use ($userObject)
		{
			return $user === $userObject;
		});
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processUserNicknameChange(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$args = $incomingIrcMessage->getArgs();
		$oldNickname = explode('!', $prefix)[0];
		$newNickname = $args[0];

		$userObject = UserCollection::getGlobalInstance()->findByNickname($oldNickname);

		if ($userObject == false)
			return;

		$userObject->setNickname($newNickname);
		EventEmitter::emit('user.nick', [$userObject, $oldNickname, $newNickname, $queue]);
	}

	/**
	 * @param IncomingIrcMessage $ircMessage
	 * @param Queue $queue
	 */
	public function processUserModeChange(IncomingIrcMessage $ircMessage, Queue $queue)
	{
		$args = $ircMessage->getArgs();
		$mode = $args[1];
		$target = !empty($args[2]) ? $args[2] : $args[0];
		$channel = !empty($args[2]) ? $args[0] : '';

		$userObject = UserCollection::getGlobalInstance()->findByNickname($target);

		if ($userObject == false)
			return;

		if (!empty($channel))
			EventEmitter::emit('user.mode.channel', [$channel, $mode, $userObject, $queue]);
		else
			EventEmitter::emit('user.mode', [$mode, $userObject, $queue]);
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
		$userObject = UserCollection::getGlobalInstance()->findByNickname($userPrefix->getNickname());

		$userObject->setHostname($newHostname);
		$userObject->setUsername($newUsername);

		EventEmitter::emit('user.host', [$userObject, $newUsername, $newHostname, $queue]);
	}
}