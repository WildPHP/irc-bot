<?php

namespace WildPHP\Core\Users;

use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

class GlobalUserCollection
{
	/**
	 * @var UserCollection
	 */
	protected static $userCollection;

	/**
	 * @return UserCollection
	 */
	public static function getUserCollection(): UserCollection
	{
		return self::$userCollection;
	}

	/**
	 * @param UserCollection $userCollection
	 */
	public static function setUserCollection(UserCollection $userCollection)
	{
		self::$userCollection = $userCollection;
	}

	/**
	 * @param string $nickname
	 *
	 * @return User
	 */
	public static function getOrCreateUserByNickname(string $nickname): User
	{
		if (self::$userCollection->isUserInCollectionByNickname($nickname))
			$userObject = self::$userCollection->findUserByNickname($nickname);
		else
		{
			$userObject = new User();
			$userObject->setNickname($nickname);
			self::$userCollection->addUser($userObject);
		}

		return $userObject;
	}

	/**
	 * @param string $nickname
	 *
	 * @return User
	 * @throws \RuntimeException
	 */
	public static function getUserByNickname(string $nickname): User
	{
		if (!self::$userCollection->isUserInCollectionByNickname($nickname))
			throw new \RuntimeException('User does not exist in collection');

		return self::$userCollection->findUserByNickname($nickname);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return false|User
	 */
	public static function getUserFromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage)
	{
		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$nickname = $prefix->getNickname();
		$userObject = self::getUserCollection()->findUserByNickname($nickname);

		if (!$userObject)
			return false;

		return $userObject;
	}

	/**
	 * @return User
	 */
	public static function getSelf()
	{
		$ownNickname = Configuration::get('currentNickname')->getValue();
		return self::getUserByNickname($ownNickname);
	}
}