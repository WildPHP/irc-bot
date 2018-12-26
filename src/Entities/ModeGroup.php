<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Entities;


class ModeGroup
{
    /**
     * @var string
     */
    private $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->getMode()
        ];
    }

    /**
     * @param array $previousState
     * @return ModeGroup
     */
    public static function fromArray(array $previousState): ModeGroup
    {
        $mode = $previousState['mode'] ?? '';
        return new ModeGroup($mode);
    }
}