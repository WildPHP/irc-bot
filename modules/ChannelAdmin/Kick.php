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
 * Class Kick
 *
 * @package WildPHP\Modules\ChannelAdmin
 */
class Kick extends IRCData
{
	/**
	 * The channel to kick someone in.
	 *
	 * @var string
	 */
	protected $channel = '';

	/**
	 * The user to kick, by username.
	 *
	 * @var string
	 */
	protected $user = '';

	/**
	 * The message to send with the kick.
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @param string $channel The channel to kick in.
	 * @param string $user    The user to kick.
	 * @param string $message An optional message to send along.
	 */
	public function __construct($channel, $user, $message = '')
	{
		$this->setChannel($channel);
		$this->setUser($user);
		$this->setMessage($message);
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
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $user
	 */
	public function setUser($user)
	{
		if (!Validation::isNickname($user))
			throw new \InvalidArgumentException($user . ' is not a valid nickname.');

		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'KICK ' . $this->getChannel() . ' ' . $this->getUser() . (!empty($this->getMessage()) ? ' :' . $this->getMessage() : '');
	}
}