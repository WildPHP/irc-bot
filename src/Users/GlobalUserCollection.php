<?php

namespace WildPHP\Core\Users;

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
	 * @return User
	 */
	public static function findOrCreateUserObject(string $nickname): User
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
	 * @param IncomingIrcMessage $incomingIrcMessage
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
}