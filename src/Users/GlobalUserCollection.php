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
}