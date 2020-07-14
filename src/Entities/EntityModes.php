<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

use InvalidArgumentException;

class EntityModes
{
    /**
     * @var array[string]
     */
    protected $modes = [];

    /**
     * EntityModes constructor.
     * @param array $modes
     */
    public function __construct(array $modes = [])
    {
        foreach ($modes as $mode => $value) {
            $this->addMode($mode, $value);
        }
    }

    /**
     * @param string $mode
     * @return bool
     */
    public function hasMode(string $mode): bool
    {
        return array_key_exists($mode, $this->modes);
    }

    /**
     * @param string $mode
     * @param string|true $value
     */
    public function addMode(string $mode, $value = ''): void
    {
        if (empty($value)) {
            $value = true;
        }

        $this->modes[$mode] = $value;
    }

    /**
     * @param string $mode
     */
    public function removeMode(string $mode): void
    {
        if (!$this->hasMode($mode)) {
            throw new InvalidArgumentException('Mode not found in this collection');
        }

        unset($this->modes[$mode]);
    }

    /**
     * @return string[]
     */
    public function getModes(): array
    {
        return array_keys($this->modes);
    }

    /**
     * @return array[string]
     */
    public function toArray(): array
    {
        return $this->modes;
    }
}
