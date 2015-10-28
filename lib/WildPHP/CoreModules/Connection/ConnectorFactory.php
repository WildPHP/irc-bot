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

use React\Dns\Resolver\Factory as ResolverFactory;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;

use WildPHP\Traits\LoopTrait;
use WildPHP\Traits\ResolverTrait;

class ConnectorFactory
{
	use LoopTrait;
	use ResolverTrait;

	public function __construct(LoopInterface $loop)
	{
		$this->setLoop($loop);

		$dnsResolverFactory = new ResolverFactory();
		$this->setResolver($dnsResolverFactory->createCached('8.8.8.8', $this->getLoop()));
	}

	/**
	 * @param string $host
	 * @param int    $port
	 *
	 * @return Promise
	 */
	public function createSecure($host, $port)
	{
		$connector = new Connector($this->getLoop(), $this->getResolver());
		$secure = new SecureConnector($connector, $this->getLoop());
		return $this->attachErrorHandler($secure, $host, $port);
	}

	/**
	 * @param string $host
	 * @param int    $port
	 *
	 * @return Promise
	 */
	public function create($host, $port)
	{
		$connector = new Connector($this->getLoop(), $this->getResolver());
		return $this->attachErrorHandler($connector, $host, $port);
	}

	/**
	 * @param ConnectorInterface $connector
	 * @param string             $host
	 * @param int                $port
	 *
	 * @return Promise
	 */
	private function attachErrorHandler(ConnectorInterface $connector, $host, $port)
	{
		return $connector->create($host, $port)->then(function (Stream $stream) use ($host, $port, &$capturedStream)
		{
			$stream->on('error', function ($error) use ($host, $port)
			{
				throw new \ErrorException('Connection to host ' . $host . ':' . $port . ' failed: ' . $error);
			});
			return $stream;
		});
	}
}