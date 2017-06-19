<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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