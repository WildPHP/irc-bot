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
namespace WildPHP\IRC\Commands;

use WildPHP\IRC\MessageLengthException;

// A NOTICE is essentially the same as a PRIVMSG, but with different command.
class Notice extends Privmsg
{
	public function __toString()
	{
		if (strlen($this->getMessage()) > 510 - 9 - strlen($this->getRecipient()))
			throw new MessageLengthException('Message passed to IRC\\Commands\\NoticePrivmsg is too long.');

		if (empty($this->getRecipient()) || empty($this->getMessage()))
			return null;

		return 'NOTICE ' . $this->getRecipient() . ' :' . $this->getMessage();
	}
}