<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 15:56
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