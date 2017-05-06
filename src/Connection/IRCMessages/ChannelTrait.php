<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 15:56
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