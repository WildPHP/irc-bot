<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Events;


class ConnectionEvent
{
    /**
     * @var null|string
     */
    private $data;

    public function __construct(?string $data = null)
    {
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->data;
    }
}