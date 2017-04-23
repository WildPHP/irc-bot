<?php

namespace WildPHP\Core\Channels;


class GlobalChannelCollection
{
	/**
	 * @var ChannelCollection
	 */
	protected static $channelCollection;

	/**
	 * @return ChannelCollection
	 */
	public static function getChannelCollection(): ChannelCollection
	{
		return self::$channelCollection;
	}

	/**
	 * @param ChannelCollection $channelCollection
	 */
	public static function setChannelCollection(ChannelCollection $channelCollection)
	{
		self::$channelCollection = $channelCollection;
	}
}