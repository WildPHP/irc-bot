<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

/**
 * Class NAMES
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: NAMES [channel](,[channel],...) ([server])
 */
class NAMES extends BaseIRCMessage implements SendableMessage
{
	protected static $verb = 'NAMES';

	use ChannelsTrait;
	use ServerTrait;

	/**
	 * NAMES constructor.
	 *
	 * @param string[]|string $channels
	 * @param string $server
	 */
	public function __construct($channels, string $server = '')
	{
		if (is_string($channels))
			$channels = [$channels];

		$this->setChannels($channels);
		$this->setServer($server);
	}

	public function __toString()
	{
		$server = !empty($this->getServer()) ? ' ' . $this->getServer() : '';
		return 'WHOWAS ' .  implode(',', $this->getChannels()) . $server;
	}
}