<?php
declare(strict_types=1);

/**
 * Copyright 2019 The WildPHP Team
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
     * @param LoopInterface $loop
     * @param bool $secure
     *
     * @param array $options
     *
     * @return ConnectorInterface
     */
    public static function create(LoopInterface $loop, bool $secure = false, array $options = []): ConnectorInterface
    {
        $connector = new Connector($loop, $options);

        if ($secure) {
            return new SecureConnector($connector, $loop);
        }

        return $connector;
    }
}