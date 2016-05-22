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

namespace WildPHP\Core\Connection;

use React\Dns\Resolver\Factory as ResolverFactory;
use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\SocketClient\Connector;
use React\SocketClient\ConnectorInterface;
use React\SocketClient\SecureConnector;
use React\Stream\Stream;

class ConnectorFactory
{
    /**
     * @var LoopInterface
     */
    protected $loop = null;

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

    /**
     * @return Resolver
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @param Resolver $resolver
     */
    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @var Resolver
     */
    protected $resolver = null;

    public function __construct(LoopInterface $loop)
    {
        $this->setLoop($loop);

        $dnsResolverFactory = new ResolverFactory();
        $this->setResolver($dnsResolverFactory->createCached('8.8.8.8', $this->getLoop()));
    }

    /**
     * @return ConnectorInterface
     */
    public function createSecure(): ConnectorInterface
    {
        $connector = new Connector($this->getLoop(), $this->getResolver());
        return new SecureConnector($connector, $this->getLoop());
    }

    /**
     * @return ConnectorInterface
     */
    public function create(): ConnectorInterface
    {
        $connector = new Connector($this->getLoop(), $this->getResolver());
        return $connector;
    }
}