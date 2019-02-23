<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;

use React\Promise\PromiseInterface;

interface CapabilityInterface
{
    /**
     * @param PromiseInterface $promise
     * @return void
     */
    public function setRequestPromise(PromiseInterface $promise): void;

    /**
     * @param callable $callback
     * @return void
     */
    public function onFinished(callable $callback): void;

    /**
     * @return bool
     */
    public function finished(): bool;
}