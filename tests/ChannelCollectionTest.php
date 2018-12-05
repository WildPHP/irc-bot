<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\ChannelCollection;

class ChannelCollectionTest extends TestCase
{
	public function testContainsChannelName()
	{
		$channelCollection = new ChannelCollection();

		$channel = new \WildPHP\Core\Observers\Channel('#test', new \WildPHP\Core\Observers\UserCollection(), new \WildPHP\Core\Observers\ChannelModes(''));
		$channelCollection->append($channel);

		self::assertFalse($channelCollection->containsChannelName('#testing'));
		self::assertTrue($channelCollection->containsChannelName('#test'));
	}

	public function testFindByChannelName()
	{
		$channelCollection = new ChannelCollection();

		$channel = new \WildPHP\Core\Observers\Channel('#test', new \WildPHP\Core\Observers\UserCollection(), new \WildPHP\Core\Observers\ChannelModes(''));
		$channelCollection->append($channel);

		self::assertFalse($channelCollection->findByChannelName('#testing'));
		self::assertSame($channel, $channelCollection->findByChannelName('#test'));
	}
}
