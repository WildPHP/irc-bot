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
     * @var EntityModes
     */
    private $modes;

    /**
     * @var EntityModes[]
     */
    private $userModes;

    /**
     * IrcChannel constructor.
     * @param string $name
     * @param int $channelId
     * @param string $topic
     * @param EntityModes $modes
     */
    public function __construct(
        string $name,
        int $channelId = 0,
        string $topic = '',
        EntityModes $modes = null,
        array $userModes = []
    ) {
        $this->name = $name;
        $this->topic = $topic;
        $this->modes = $modes ?? new EntityModes();
        $this->channelId = $channelId;
        $this->userModes = $userModes;
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
     * @return EntityModes
     */
    public function getModes(): EntityModes
    {
        return $this->modes;
    }

    /**
     * @param EntityModes $modes
     */
    public function setModes(EntityModes $modes): void
    {
        $this->modes = $modes;
    }

    /**
     * @param int $userId
     * @return mixed|EntityModes
     */
    public function getModesForUserId(int $userId)
    {
        if (!array_key_exists($userId, $this->userModes)) {
            $this->userModes[$userId] = new EntityModes();
        }

        return $this->userModes[$userId];
    }

    /**
     * @return EntityModes[]
     */
    public function getUserModes(): array
    {
        return $this->userModes;
    }

    /**
     * @param EntityModes[] $userModes
     */
    public function setUserModes(array $userModes): void
    {
        $this->userModes = $userModes;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $userModeArray = [];

        foreach ($this->userModes as $userId => $modes) {
            $userModeArray[$userId] = $modes->toArray();
        }

        return [
            'id' => $this->getChannelId(),
            'name' => $this->getName(),
            'topic' => $this->getTopic(),
            'modes' => $this->getModes()->toArray(),
            'userModes' => $userModeArray
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
        $modes = new EntityModes((array)($previousState['modes'] ?? []));
        $userModes = [];
        if (is_array($userModes) && !empty($previousState['userModes'])) {
            foreach ($previousState['userModes'] as $userId => $userModeList) {
                $userModes[$userId] = new EntityModes($userModeList);
            }
        }
        return new IrcChannel($name, $channelId, $topic, $modes, $userModes);
    }
}
