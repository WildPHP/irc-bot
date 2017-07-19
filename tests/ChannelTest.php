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
use WildPHP\Core\Channels\Channel;

class ChannelTest extends TestCase
{
	protected $container;
	public function setUp()
	{
		$this->container = new \WildPHP\Core\ComponentContainer();
	}

	public function testGetSetChannelName()
	{
		$userCollection = new \WildPHP\Core\Users\UserCollection($this->container);
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($this->container);

		$name = '#someChannel';
		$channel = new Channel($name, $userCollection, $channelModes);

		static::assertEquals($name, $channel->getName());
	}

	public function testGetSetChannelTopic()
	{
		$userCollection = new \WildPHP\Core\Users\UserCollection($this->container);
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($this->container);
		$channel = new Channel('#someChannel', $userCollection, $channelModes);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		static::assertEquals($topic, $channel->getTopic());
	}

	public function testGetSetChannelDescription()
	{
		$userCollection = new \WildPHP\Core\Users\UserCollection($this->container);
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($this->container);
		$channel = new Channel('#someChannel', $userCollection, $channelModes);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		static::assertEquals($topic, $channel->getTopic());
	}
}
