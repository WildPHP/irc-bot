<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace WildPHP\Modules\ChannelAdmin;

use WildPHP\IRC\IRCData;
use WildPHP\Validation;

/**
 * Class Mode
 *
 * @package WildPHP\Modules\ChannelAdmin
 */
class Mode extends IRCData
{
	/**
	 * The mode(s) to set.
	 *
	 * @var string
	 */
	protected $modes;

	/**
	 * The channel to set this mode to.
	 *
	 * @var string
	 */
	protected $channel;

	/**
	 * The mode params this applies to. Optional.
	 *
	 * @var string
	 */
	protected $modeparams = '';

	/**
	 * @param string $modes      The modes to set.
	 * @param string $channel    The channel to apply these modes to.
	 * @param string $modeparams The parameters for this mode (refer to RFC 2812, section 3.2.3)
	 */
	public function __construct($modes, $channel, $modeparams = '')
	{
		$this->setModes($modes);
		$this->setChannel($channel);
		$this->setModeparams($modeparams);
	}

	/**
	 * @return string
	 */
	public function getModes()
	{
		return $this->modes;
	}

	/**
	 * @param string $modes
	 */
	public function setModes($modes)
	{
		if (preg_match('/^[\+\-a-zA-Z]+$/', $modes) === false)
			throw new InvalidModeOperationException('Mode operation ' . $modes . ' is invalid.');

		$this->modes = $modes;
	}

	/**
	 * @return string
	 */
	public function getChannel()
	{
		return $this->channel;
	}

	/**
	 * @param string $channel
	 */
	public function setChannel($channel)
	{
		if (!Validation::isChannel($channel))
			throw new \InvalidArgumentException($channel . ' is not a valid channel.');

		$this->channel = $channel;
	}

	/**
	 * @return string
	 */
	public function getModeparams()
	{
		return $this->modeparams;
	}

	/**
	 * @param string $modeparams
	 */
	public function setModeparams($modeparams)
	{
		$this->modeparams = $modeparams;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'MODE ' . $this->getChannel() . ' ' . $this->getModes() . (!empty($this->getModeparams()) ? ' ' . $this->getModeparams() : '');
	}
}

class InvalidModeOperationException extends \RuntimeException
{
}