<?php
declare(strict_types=1);
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Storage;


class StoredEntity implements StoredEntityInterface
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $id;

    /**
     * StoredEntity constructor.
     * @param array $data
     * @param int $id
     */
    public function __construct(array $data, int $id = 0)
    {
        $this->data = $data;
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
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
}