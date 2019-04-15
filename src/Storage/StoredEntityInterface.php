<?php
/**
 * Copyright 2019 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Storage;

interface StoredEntityInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $entityId
     */
    public function setId(int $entityId): void;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     */
    public function setData(array $data): void;
}
