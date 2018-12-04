<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;

interface IrcConnectionInterface
{
    /**
     * @param string $data
     *
     * @return \React\Promise\PromiseInterface
     */
    public function write(string $data): PromiseInterface;

    /**
     * @param string $data
     */
    public function incomingData(string $data);

    /**
     * @param QueueItem $queueItem
     */
    public function writeQueueItem(QueueItem $queueItem);

    /**
     * @param ConnectorInterface $connectorInterface
     *
     * @return \React\Promise\PromiseInterface
     */
    public function connect(ConnectorInterface $connectorInterface);

    /**
     * @return \React\Promise\PromiseInterface
     */
    public function close();

    /**
     * @return ConnectionDetails
     */
    public function getConnectionDetails(): ConnectionDetails;
}