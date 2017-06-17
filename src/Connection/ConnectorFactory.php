<?php

/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Connection;

use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use React\Socket\SecureConnector;

class ConnectorFactory
{
	/**
	 * @var LoopInterface
	 */
	protected $loop = null;

	/**
	 * ConnectorFactory constructor.
	 *
	 * @param LoopInterface $loop
	 */
	public function __construct(LoopInterface $loop)
	{
		$this->setLoop($loop);
	}

	/**
	 * @return ConnectorInterface
	 */
	public function createSecure(): ConnectorInterface
	{
		$connector = $this->create();
		return new SecureConnector($connector, $this->getLoop());
	}

	/**
	 * @return ConnectorInterface
	 */
	public function create(): ConnectorInterface
	{
		return new Connector($this->getLoop());
	}

	/**
	 * @return LoopInterface
	 */
	public function getLoop()
	{
		return $this->loop;
	}

	/**
	 * @param LoopInterface $loop
	 */
	public function setLoop($loop)
	{
		$this->loop = $loop;
	}
}