<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

trait ChannelTrait
{
	protected $channel = '';

	/**
	 * @return string
	 */
	public function getChannel(): string
	{
		return $this->channel;
	}

	/**
	 * @param string $channel
	 */
	public function setChannel(string $channel)
	{
		$this->channel = $channel;
	}
}