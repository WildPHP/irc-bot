<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Entities;


class IrcChannel
{
    private $id = 0;
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $topic;

    /**
     * @var array
     */
    private $modes;

    /**
     * IrcChannel constructor.
     * @param string $name
     * @param int $id
     * @param string $topic
     * @param array $modes
     */
    public function __construct(string $name, int $id = 0, string $topic = '', array $modes = [])
    {

        $this->name = $name;
        $this->topic = $topic;
        $this->modes = $modes;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @return array
     */
    public function getModes(): array
    {
        return $this->modes;
    }

    /**
     * @param array $modes
     */
    public function setModes(array $modes): void
    {
        $this->modes = $modes;
    }
}