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

namespace WildPHP\CoreModules\Connection;

use WildPHP\BaseModuleInterface;
use WildPHP\Modules\DataObject;

class IrcDataObject extends DataObject
{
	/**
	 * @var array
	 */
	protected $message = [];

	/**
	 * @param BaseModuleInterface $self
	 * @param array $message
	 */
	public function __construct(BaseModuleInterface $self, array $message)
	{
		parent::__construct($self);

		$this->setMessage($message);
	}

	protected function getItem($key, $fallback = '')
	{
		if (!array_key_exists($key, $this->message))
			return $fallback;

		return $this->message[$key];
	}

	/**
	 * @param array $message
	 */
	public function setMessage(array $message)
	{
		$this->message = $message;
	}

	/**
	 * @return array
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getIrcMessage()
	{
		return $this->getItem('message');
	}

	/**
	 * @return string
	 */
	public function getCommand()
	{
		return $this->getItem('command');
	}

	/**
	 * @return array|string
	 */
	public function getParams()
	{
		return $this->getItem('params');
	}

	/**
	 * @return array
	 */
	public function getTargets()
	{
		return $this->getItem('targets', []);
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->getItem('prefix');
	}
}