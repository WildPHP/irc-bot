<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Helpers;

/**
 * Class Argumented
 * @package WildPHP\Core\Helpers
 *
 * This class cannot extend from Model as that will create a deadlock.
 */
class Argumented
{
    /**
     * @var string
     */
    protected $verb = '';

    /**
     * @var string[]
     */
    protected $arguments = [];

    /**
     * Argumented constructor.
     * @param string $verb
     * @param string[] $arguments
     */
    public function __construct(string $verb, array $arguments)
    {
        $this->verb = $verb;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
