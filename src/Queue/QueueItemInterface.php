<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Queue;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;

interface QueueItemInterface
{
    /**
     * @param Deferred $deferred
     */
    public function setDeferred(Deferred $deferred): void;

    /**
     * @return Deferred
     */
    public function getDeferred(): Deferred;

    /**
     * @return PromiseInterface
     */
    public function getPromise(): PromiseInterface;
}
