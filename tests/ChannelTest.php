<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 1-5-2017
 * Time: 13:03
 */

use WildPHP\Core\Channels\Channel;

class ChannelTest extends PHPUnit_Framework_TestCase
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
		$channel = new Channel($userCollection, $channelModes);

		$name = '#someChannel';
		$channel->setName($name);

		$this->assertEquals($name, $channel->getName());
	}

	public function testGetSetChannelTopic()
	{
		$userCollection = new \WildPHP\Core\Users\UserCollection($this->container);
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($this->container);
		$channel = new Channel($userCollection, $channelModes);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		$this->assertEquals($topic, $channel->getTopic());
	}

	public function testGetSetChannelDescription()
	{
		$userCollection = new \WildPHP\Core\Users\UserCollection($this->container);
		$channelModes = new \WildPHP\Core\Channels\ChannelModes($this->container);
		$channel = new Channel($userCollection, $channelModes);

		$topic = 'This is a test topic';
		$channel->setTopic($topic);

		$this->assertEquals($topic, $channel->getTopic());
	}
}
