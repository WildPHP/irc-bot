<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;


trait ChannelsTrait
{
	protected $channels = [];

	/**
	 * @return array
	 */
	public function getChannels(): array
	{
		return $this->channels;
	}

	/**
	 * @param array $channels
	 */
	public function setChannels(array $channels)
	{
		$this->channels = $channels;
	}
}