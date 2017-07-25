<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 25-7-2017
 * Time: 18:15
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Channels\ChannelStateManager;
use WildPHP\Core\EventEmitter;

class ChannelStateManagerTest extends TestCase
{
	public function initContainer()
	{
		$eventEmitter = new EventEmitter();
		$logger = new \WildPHP\Core\Logger\Logger('wildphp');
		$channelCollection = new \WildPHP\Core\Channels\ChannelCollection();
		$channel = new \WildPHP\Core\Channels\Channel('#test', new \WildPHP\Core\Users\UserCollection(), new \WildPHP\Core\Channels\ChannelModes(''));
		$channelCollection->append($channel);

		$componentContainer = new \WildPHP\Core\ComponentContainer();
		$componentContainer->add($eventEmitter);
		$componentContainer->add($logger);
		$componentContainer->add($channelCollection);
		return $componentContainer;
	}
	public function init(\WildPHP\Core\ComponentContainer $componentContainer): ChannelStateManager
	{
		return new ChannelStateManager($componentContainer);
	}

	public function testTopicChange()
	{
		$rpl_topic = new \WildPHP\Core\Connection\IRCMessages\RPL_TOPIC();
		$rpl_topic->setChannel('#test');
		$rpl_topic->setMessage('NewTopic');

		$componentContainer = $this->initContainer();
		$channelStateManager = $this->init($componentContainer);

		EventEmitter::fromContainer($componentContainer)->once('channel.topic', function (\WildPHP\Core\Channels\Channel $channel, string $topic)
		{
			self::assertEquals('#test', $channel->getName());
			self::assertEquals('NewTopic', $topic);
		});

		$channelStateManager->processChannelTopicChange($rpl_topic);
	}
}
