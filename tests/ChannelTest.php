<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\Channel;

class ChannelTest extends TestCase
{

	public function testGetSetChannelName()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();

		$name = '#someChannel';
		$channel = new Channel($name, $userCollection);

		static::assertEquals($name, $channel->getName());
	}

	public function testGetSetChannelTopic()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();
		$channel = new Channel('#someChannel', $userCollection);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		static::assertEquals($topic, $channel->getTopic());
	}

	public function testGetSetChannelDescription()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();
		$channel = new Channel('#someChannel', $userCollection);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		static::assertEquals($topic, $channel->getTopic());
	}

	public function testGetSetCreatedBy()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();
		$channel = new Channel('#someChannel', $userCollection);
		
		$createdBy = 'SomeUser';
		$channel->setCreatedBy($createdBy);
		
		static::assertEquals($createdBy, $channel->getCreatedBy());
	}

	public function testGetSetCreatedTime()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();
		$channel = new Channel('#someChannel', $userCollection);
		
		$createdTime = 100;
		$channel->setCreatedTime($createdTime);
		
		static::assertEquals($createdTime, $channel->getCreatedTime());
	}

	public function testGetSetUserCollection()
	{
		$userCollection = new \WildPHP\Core\Observers\UserCollection();
		$channel = new Channel('#someChannel', $userCollection);

		static::assertSame($userCollection, $channel->getUserCollection());
	}

	public function testIsValidName()
	{
		self::assertTrue(Channel::isValidName('#test', '#'));
		self::assertFalse(Channel::isValidName('#test', '!'));
	}
}
