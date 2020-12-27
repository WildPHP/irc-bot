<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Connection;

use InvalidArgumentException;
use WildPHP\Core\Helpers\Validation;

class ConnectionDetails
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $realname;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $wantedNickname;

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var array
     */
    protected $contextOptions = [];

    /**
     * ConnectionDetails constructor.
     * @param string $username
     * @param string $hostname
     * @param string $address
     * @param int $port
     * @param string $realname
     * @param string $password
     * @param string $wantedNickname
     * @param bool $secure
     * @param array $contextOptions
     */
    public function __construct(
        string $username,
        string $hostname,
        string $address,
        int $port,
        string $realname,
        string $password,
        string $wantedNickname,
        bool $secure = false,
        array $contextOptions = []
    ) {
        $this->username = $username;
        $this->hostname = $hostname;
        $this->address = $address;
        $this->port = $port;
        $this->realname = $realname;
        $this->password = $password;
        $this->wantedNickname = $wantedNickname;
        $this->secure = $secure;
        $this->contextOptions = $contextOptions;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getConnectionString(): string
    {
        return $this->getAddress() . ':' . $this->getPort();
    }

    /**
     * @return string
     */
    public function getRealname(): string
    {
        return $this->realname;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getWantedNickname(): string
    {
        return $this->wantedNickname;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return array
     */
    public function getContextOptions(): array
    {
        return $this->contextOptions;
    }

    /**
     * @param array $configuration
     * @return ConnectionDetails
     */
    public static function fromArray(array $configuration): ConnectionDetails
    {
        if (!Validation::arrayHasKeys($configuration, ['username', 'server', 'port', 'realname', 'nickname'])) {
            throw new InvalidArgumentException('Missing keys in configuration given to ConnectionDetails::fromArray');
        }

        return new ConnectionDetails(
            $configuration['username'],
            gethostname(),
            $configuration['server'],
            $configuration['port'],
            $configuration['realname'],
            $configuration['password'] ?? '',
            $configuration['nickname'][0],
            $configuration['secure'] ?? false,
            $configuration['options'] ?? []
        );
    }
}
