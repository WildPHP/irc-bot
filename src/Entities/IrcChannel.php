<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Entities;

class IrcChannel
{
    /**
     * @var int
     */
    private $channelId;

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
     * @param int $channelId
     * @param string $topic
     * @param array $modes
     */
    public function __construct(string $name, int $channelId = 0, string $topic = '', array $modes = [])
    {
        $this->name = $name;
        $this->topic = $topic;
        $this->modes = $modes;
        $this->channelId = $channelId;
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return $this->channelId;
    }

    /**
     * @param int $channelId
     */
    public function setChannelId(int $channelId): void
    {
        $this->channelId = $channelId;
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getChannelId(),
            'name' => $this->getName(),
            'topic' => $this->getTopic(),
            'modes' => $this->getModes()
        ];
    }

    /**
     * @param array $previousState
     * @return IrcChannel
     */
    public static function fromArray(array $previousState): IrcChannel
    {
        $name = $previousState['name'] ?? '';
        $channelId = (int)($previousState['id'] ?? 0);
        $topic = $previousState['topic'] ?? '';
        $modes = (array)($previousState['modes'] ?? []);
        return new IrcChannel($name, $channelId, $topic, $modes);
    }
}
