<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Security;

use WildPHP\Core\DataStorage\DataStorage;
use WildPHP\Core\Users\User;

class PermissionGroup
{
	/**
	 * @var string[]
	 */
	protected $userCollection = [];

	/**
	 * @var string[]
	 */
	protected $allowedPermissions = [];

	/**
	 * @var bool
	 */
	protected $canHaveMembers = true;

	/**
	 * @var string[]
	 */
	protected $channels = [];

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * PermissionGroup constructor.
	 * @param string $name
	 * @param bool $load
	 */
	public function __construct(string $name, $load = false)
	{
		$this->setName($name);

		if ($load)
			$this->loadPermissionsFromStorage();
	}

	/**
	 *
	 */
	public function loadPermissionsFromStorage()
	{
		$dataStorage = new DataStorage('permissiongroups');

		if (!in_array($this->getName(), $dataStorage->getKeys()))
			return;

		$data = $dataStorage->get($this->getName());
        $this->fromArray($data);
	}

	/**
	 *
	 */
	public function save()
	{
		$dataStorage = new DataStorage('permissiongroups');

		$dataStorage->set($this->getName(), $this->toArray());
	}

	/**
	 * @param string $ircAccount
	 * @return bool
	 */
	public function isMemberByIrcAccount(string $ircAccount)
	{
		return in_array($ircAccount, $this->userCollection);
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isMember(User $user)
	{
		return in_array($user->getIrcAccount(), $this->userCollection);
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function addMember(User $user)
	{
		$ircAccount = $user->getIrcAccount();
		if (empty($ircAccount))
			return false;

		if ($this->isMemberByIrcAccount($ircAccount))
			return true;

		$this->userCollection[] = $ircAccount;

		return true;
	}

	/**
	 * @param string $ircAccount
	 * @return bool
	 */
	public function addMemberByIrcAccount(string $ircAccount)
	{
		if ($this->isMemberByIrcAccount($ircAccount))
			return true;

		$this->userCollection[] = $ircAccount;

		return true;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function removeMember(User $user)
	{
		$ircAccount = $user->getIrcAccount();
		if (empty($ircAccount))
			return false;

		if (!$this->isMemberByIrcAccount($ircAccount))
			return true;

		unset($this->userCollection[array_search($ircAccount, $this->userCollection)]);

		return true;
	}

	/**
	 * @param string $ircAccount
	 * @return bool
	 */
	public function removeMemberByIrcAccount(string $ircAccount)
	{
		if (!$this->isMemberByIrcAccount($ircAccount))
			return true;

		unset($this->userCollection[array_search($ircAccount, $this->userCollection)]);

		return true;
	}

    /**
     * @param string $channelName
     * @return bool
     */
    public function containsChannel(string $channelName): bool
	{
		return in_array($channelName, $this->channels);
	}

    /**
     * @param string $channelName
     * @return bool
     */
    public function addChannel(string $channelName): bool
	{
		if ($this->containsChannel($channelName))
			return false;

		$this->channels[] = $channelName;
		return true;
	}

    /**
     * @param string $channelName
     * @return bool
     */
    public function removeChannel(string $channelName): bool
	{
		if (!$this->containsChannel($channelName))
			return false;

		unset($this->channels[array_search($channelName, $this->channels)]);
		return true;
	}

    /**
     * @return array
     */
    public function listChannels(): array
	{
		return $this->channels;
	}

	/**
	 * @return string[]
	 */
	public function getUserCollection(): array
	{
		return $this->userCollection;
	}

	/**
	 * @param array $members
	 */
	protected function setUserCollection(array $members)
	{
		$this->userCollection = $members;
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
	public function setName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function getCanHaveMembers(): bool
	{
		return $this->canHaveMembers;
	}

	/**
	 * @param bool $canHaveMembers
	 */
	public function setCanHaveMembers(bool $canHaveMembers)
	{
		$this->canHaveMembers = $canHaveMembers;
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function containsPermission(string $permission): bool
	{
		return in_array($permission, $this->allowedPermissions);
	}

    /**
     * @param string $permission
     * @param string $channel
     * @return bool
     */
    public function hasPermission(string $permission, string $channel = ''): bool
	{
		$hasPermission = $this->containsPermission($permission);
		$isCorrectChannel = empty($this->listChannels()) ? true : !empty($channel) ? $this->containsChannel($channel) : false;
		return $hasPermission && $isCorrectChannel;
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function addPermission(string $permission): bool
	{
		if ($this->containsPermission($permission))
			return false;

		$this->allowedPermissions[] = $permission;

		return true;
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	public function removePermission(string $permission): bool
	{
		if (!$this->containsPermission($permission))
			return false;

		unset($this->allowedPermissions[array_search($permission, $this->allowedPermissions)]);

		return true;
	}

	/**
	 * @return array
	 */
	public function listPermissions(): array
	{
		return $this->allowedPermissions;
	}

	public function toArray(): array
    {
        return [
            'canHaveMembers' => (int) $this->getCanHaveMembers(),
            'members' => $this->getUserCollection(),
            'allowedPermissions' => $this->listPermissions(),
            'linkedChannels' => $this->listChannels(),
        ];
    }

    public function fromArray(array $data)
    {
        $this->setCanHaveMembers((bool) $data['canHaveMembers']);
        $this->setUserCollection((array) $data['members']);

        $permissions = $data['allowedPermissions'];
        foreach ($permissions as $permission)
        {
            $this->addPermission($permission);
        }

        $channels = $data['linkedChannels'];
        foreach ($channels as $channel)
        {
            $this->addChannel($channel);
        }
    }
}