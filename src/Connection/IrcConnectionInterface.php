<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection;

use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;

interface IrcConnectionInterface
{
    /**
     * @param string $data
     *
     * @return PromiseInterface
     */
    public function write(string $data): PromiseInterface;

    /**
     * @param string $data
     */
    public function incomingData(string $data): void;

    /**
     * @param ConnectorInterface $connectorInterface
     *
     * @return PromiseInterface
     */
    public function connect(ConnectorInterface $connectorInterface): PromiseInterface;

    /**
     * @return PromiseInterface
     */
    public function close(): PromiseInterface;

    /**
     * @return ConnectionDetails
     */
    public function getConnectionDetails(): ConnectionDetails;
}
