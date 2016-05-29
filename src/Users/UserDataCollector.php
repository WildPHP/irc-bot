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


use WildPHP\Core\Connection\CapabilityHandler;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

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
		
		EventEmitter::on('irc.line.in.366', __NAMESPACE__ . '\UserDataCollector::sendWhox');
		EventEmitter::on('irc.line.in.354', __NAMESPACE__ . '\UserDataCollector::processWhox');
		EventEmitter::on('irc.line.in.quit', __NAMESPACE__ . '\UserDataCollector::processQuit');
		EventEmitter::on('irc.line.in.join', __NAMESPACE__ . '\UserDataCollector::processJoin');
		EventEmitter::on('irc.line.in.part', __NAMESPACE__ . '\UserDataCollector::processPart');
		EventEmitter::on('irc.line.in.nick', __NAMESPACE__ . '\UserDataCollector::processNick');
	}

	public static function sendWhox(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$channel = $incomingIrcMessage->getArgs()[1];
		$queue->who($channel, '%na');
	}

	public static function processWhox(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$args = $incomingIrcMessage->getArgs();
		$nickname = $args[1];
		$accountname = $args[2];
		$userObject = GlobalUserCollection::findOrCreateUserObject($nickname);

		self::updateAccountnameForUser($userObject, $accountname, $queue);
	}

	protected static function updateAccountnameForUser(User $userObject, string $accountname, Queue $queue)
	{
		if ($userObject->getIrcAccount() == $accountname)
			return;

		$userObject->setIrcAccount($accountname);
		self::$userCollection->addUser($userObject);
		$nickname = $userObject->getNickname();
		EventEmitter::emit('user.account.changed', [$nickname, $accountname, $queue]);
	}

	public static function processPart(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];

		$userObject = self::$userCollection->findUserByNickname($nickname);
		
		if ($userObject == false)
			return;

		EventEmitter::emit('user.part', [$userObject, $channel, $queue]);

		if ($userObject->getChannelCollection()->count() == 0)
			self::$userCollection->removeUser($userObject);
	}

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

	public static function processJoin(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$prefix = $incomingIrcMessage->getPrefix();
		$nickname = explode('!', $prefix)[0];
		$args = $incomingIrcMessage->getArgs();
		$channel = $args[0];
		
		$userObject = GlobalUserCollection::findOrCreateUserObject($nickname);
		
		if ($userObject == false)
			return;
		
		EventEmitter::emit('user.join', [$userObject, $channel, $queue]);

		if (!CapabilityHandler::isCapabilityActive('extended-join'))
		{
			$queue->who($nickname, '%na');
			return;
		}

		$accountname = $args[1];

		self::updateAccountnameForUser($userObject, $accountname, $queue);
	}

	public static function processNick(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		Logger::debug('Nickname change detected', [$incomingIrcMessage]);
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
}