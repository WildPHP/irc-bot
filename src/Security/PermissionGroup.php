<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;

use WildPHP\Core\Collection;
use WildPHP\Core\DataStorage\DataStorageFactory;

class PermissionGroup
{
	/**
	 * @var Collection
	 */
	protected $userCollection;

	/**
	 * @var Collection
	 */
	protected $allowedPermissions;

	/**
	 * @var Collection
	 */
	protected $channelCollection;

	/**
	 * @var bool
	 */
	protected $canHaveMembers = true;

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * PermissionGroup constructor.
	 *
	 * @param string $name
	 * @param bool $load
	 */
	public function __construct(string $name, $load = false)
	{
		$this->setAllowedPermissions(new Collection('string'));
		$this->setChannelCollection(new Collection('string'));
		$this->setUserCollection(new Collection('string'));

		$this->setName($name);

		if ($load)
			$this->loadPermissionsFromStorage();
	}

	/**
	 * @param string $permission
	 * @param string $channel
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission, string $channel = ''): bool
	{
		$hasPermission = $this->getAllowedPermissions()->contains($permission);
		$hasNoChannels = !$this->getChannelCollection()->valid();
		$isCorrectChannel = $hasNoChannels ? true : $this->getChannelCollection()->contains($channel);
		return $hasPermission && $isCorrectChannel;
	}

	public function loadPermissionsFromStorage()
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');

		if (!in_array($this->getName(), $dataStorage->getKeys()))
			return;

		$data = $dataStorage->get($this->getName());
		$this->fromArray($data);
	}

	public function save()
	{
		$dataStorage = DataStorageFactory::getStorage('permissiongroups');

		$dataStorage->set($this->getName(), $this->toArray());
	}

	/**
	 * @return Collection
	 */
	public function getUserCollection(): Collection
	{
		return $this->userCollection;
	}

	/**
	 * @param Collection $members
	 */
	protected function setUserCollection(Collection $members)
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
	 * @return Collection
	 */
	public function getAllowedPermissions(): Collection
	{
		return $this->allowedPermissions;
	}

	/**
	 * @param Collection $allowedPermissions
	 */
	public function setAllowedPermissions(Collection $allowedPermissions)
	{
		$this->allowedPermissions = $allowedPermissions;
	}

	/**
	 * @return Collection
	 */
	public function getChannelCollection(): Collection
	{
		return $this->channelCollection;
	}

	/**
	 * @param Collection $channelCollection
	 */
	public function setChannelCollection(Collection $channelCollection)
	{
		$this->channelCollection = $channelCollection;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'canHaveMembers' => (int) $this->getCanHaveMembers(),
			'userCollection' => $this->getUserCollection()->serialize(),
			'allowedPermissions' => $this->getAllowedPermissions()->serialize(),
			'channelCollection' => $this->getChannelCollection()->serialize(),
		];
	}

	/**
	 * @param array $data
	 */
	public function fromArray(array $data)
	{
		// Gracefully migrate between data types.
		if (array_key_exists('members', $data))
		{
			$userCollection = new Collection('string', $data['members']);
			$this->setUserCollection($userCollection);

			$channelCollection = new Collection('string', $data['linkedChannels']);
			$this->setChannelCollection($channelCollection);

			$allowedPermissions = new Collection('string', $data['allowedPermissions']);
			$this->setAllowedPermissions($allowedPermissions);
			$this->save();
			return;
		}

		$this->setCanHaveMembers((bool) $data['canHaveMembers']);

		$userCollection = new Collection('string');
		$userCollection->unserialize($data['userCollection']);
		$this->setUserCollection($userCollection);

		$channelCollection = new Collection('string');
		$channelCollection->unserialize($data['channelCollection']);
		$this->setChannelCollection($channelCollection);

		$allowedPermissions = new Collection('string');
		$allowedPermissions->unserialize($data['allowedPermissions']);
		$this->setAllowedPermissions($allowedPermissions);
	}
}