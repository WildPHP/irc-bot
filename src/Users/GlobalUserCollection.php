<?php

namespace WildPHP\Core\Users;

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
}