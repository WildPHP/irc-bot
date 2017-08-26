<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Commands;


use WildPHP\Core\Channels\ChannelCollection;

class JoinedChannelNameParameter extends Parameter
{
	/**
	 * JoinedChannelNameParameter constructor.
	 *
	 * @param ChannelCollection $channelCollection
	 */
	public function __construct(ChannelCollection $channelCollection)
	{
		parent::__construct(function (string $value) use ($channelCollection)
		{
			return !empty($channelCollection->findByChannelName($value));
		});
	}
}