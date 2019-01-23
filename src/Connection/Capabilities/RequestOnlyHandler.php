<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Capabilities;


use React\Promise\PromiseInterface;

class RequestOnlyHandler implements CapabilityInterface
{
    /**
     * @var callable
     */
    private $callback;

    private $finished = false;

    /**
     * @param PromiseInterface $promise
     * @return void
     */
    public function setRequestPromise(PromiseInterface $promise)
    {
        $closure = function () {
            $this->finished = true;
            ($this->callback)();
        };

        $promise->then($closure, $closure);
    }

    /**
     * @return bool
     */
    public function finished(): bool
    {
        return $this->finished;
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function onFinished(callable $callback)
    {
        $this->callback = $callback;
    }
}