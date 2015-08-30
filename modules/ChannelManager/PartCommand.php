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
namespace WildPHP\Modules\ChannelManager;

class PartCommand extends JoinCommand
{
	/**
	 * The message to send along. Is optional.
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * @param string|string[] $channels The password to send.
	 * @param string          $message  The message to send.
	 */
	public function __construct($channels, $message = '')
	{
		parent::__construct($channels);
		$this->setMessage($message);
	}

	/**
	 * Sets the message to send.
	 *
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * Gets the message.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	public function __toString()
	{
		return 'PART ' . implode(',', $this->getChannels()) . (!empty($this->getMessage()) ? ' :' . $this->getMessage() : '');
	}
}