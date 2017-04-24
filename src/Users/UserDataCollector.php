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

namespace WildPHP\Core\Users;


use WildPHP\Core\Channels\GlobalChannelCollection;
use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;

class UserDataCollector
{
	/**
	 * @var UserCollection
	 */
	protected static $userCollection = null;

	public static function initialize()
	{
		self::$userCollection = new UserCollection();
		GlobalUserCollection::setUserCollection(self::$userCollection);

		EventEmitter::on('irc.line.in.366', __CLASS__ . '::sendWhox');
		EventEmitter::on('irc.line.in.354', __CLASS__ . '::processWhox');
		EventEmitter::on('irc.line.in.quit', __CLASS__ . '::processQuit');
		EventEmitter::on('irc.line.in.join', __CLASS__ . '::processJoin');
		EventEmitter::on('irc.line.in.part', __CLASS__ . '::processPart');
		EventEmitter::on('irc.line.in.nick', __CLASS__ . '::processNick');
		EventEmitter::on('irc.line.in.mode', __CLASS__ . '::processMode');

		// Kicks can be handled in the same way parts are.
		EventEmitter::on('irc.line.in.kick', __CLASS__ . '::processPart');
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function sendWhox(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$channel = $incomingIrcMessage->getArgs()[1];
		$queue->who($channel, '%nuhaf');
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processWhox(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$args = $incomingIrcMessage->getArgs();
		$username = $args[1];
		$hostname = $args[2];
		$nickname = $args[3];
		$accountname = $args[5];
		$userObject = GlobalUserCollection::getOrCreateUserByNickname($nickname);

		$userObject->setUsername($username);
		$userObject->setHostname($hostname);
		$userObject->setIrcAccount($accountname);

		self::$userCollection->addUser($userObject);
		EventEmitter::emit('user.account.changed', [$userObject, $queue]);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processPart(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];

		if ($incomingIrcMessage->getVerb() == 'KICK')
			$nickname = $args[1];

		$userObject = self::$userCollection->findUserByNickname($nickname);

		if ($userObject == false)
			return;

		EventEmitter::emit('user.part', [$userObject, $channel, $queue]);

		if ($userObject->getChannelCollection()->count() == 0)
			self::$userCollection->removeUser($userObject);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processQuit(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];

		$userObject = self::$userCollection->findUserByNickname($nickname);

		if ($userObject == false)
			return;

		EventEmitter::emit('user.quit', [$userObject, $queue]);
		self::$userCollection->removeUser($userObject);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processJoin(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];
		$accountname = $args[1];

		$userObject = GlobalUserCollection::getOrCreateUserByNickname($nickname);

		if ($userObject == false)
			return;

		$userObject->setIrcAccount($accountname);

		EventEmitter::emit('user.join', [$userObject, $channel, $queue]);
		$queue->who($nickname, '%nuhaf');
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processNick(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$args = $incomingIrcMessage->getArgs();
		$oldNickname = explode('!', $prefix)[0];
		$newNickname = $args[0];

		$userObject = self::$userCollection->findUserByNickname($oldNickname);

		if ($userObject == false)
			return;

		$userObject->setNickname($newNickname);
		self::$userCollection->removeUserByNickname($oldNickname);
		self::$userCollection->addUser($userObject);

		EventEmitter::emit('user.nick', [$oldNickname, $newNickname, $queue]);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function processMode(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$args = $incomingIrcMessage->getArgs();
		$mode = $args[1];
		$target = !empty($args[2]) ? $args[2] : $args[0];
		$channel = !empty($args[2]) ? $args[0] : '';

		$userObject = self::$userCollection->findUserByNickname($target);

		if ($userObject == false)
			return;

		if (!empty($channel))
			EventEmitter::emit('user.mode.channel', [$channel, $mode, $userObject, $queue]);
		else
			EventEmitter::emit('user.mode', [$mode, $userObject, $queue]);
	}
}