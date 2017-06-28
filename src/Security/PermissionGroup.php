<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Security;

use Evenement\EventEmitterTrait;
use ValidationClosures\Types;
use Yoshi2889\Collections\Collection;

class PermissionGroup
{
	use EventEmitterTrait;

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
	 * PermissionGroup constructor.
	 *
	 * @param array $previousState
	 */
	public function __construct(array $previousState = [])
	{
		$this->setAllowedPermissions(new Collection(Types::string()));
		$this->setChannelCollection(new Collection(Types::string()));
		$this->setUserCollection(new Collection(Types::string()));

		if (!empty($previousState))
			$this->fromArray($previousState);
	}

	/**
	 * @param string $permission
	 * @param string $channel
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission, string $channel = ''): bool
	{
		$hasPermission = $this->getAllowedPermissions()
			->contains($permission);

		$hasNoChannels = empty($this->getChannelCollection()
			->values());

		$isCorrectChannel = $hasNoChannels ? true : $this->getChannelCollection()
			->contains($channel);

		return $hasPermission && $isCorrectChannel;
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
		$members->on('changed', function ()
		{
			$this->emit('changed');
		});
		$this->userCollection = $members;
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
	protected function setCanHaveMembers(bool $canHaveMembers)
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
	protected function setAllowedPermissions(Collection $allowedPermissions)
	{
		$allowedPermissions->on('changed', function ()
		{
			$this->emit('changed');
		});
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
	protected function setChannelCollection(Collection $channelCollection)
	{
		$channelCollection->on('changed', function ()
		{
			$this->emit('changed');
		});
		$this->channelCollection = $channelCollection;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'canHaveMembers' => (int) $this->getCanHaveMembers(),
			'userCollection' => $this->getUserCollection()
				->values(),
			'allowedPermissions' => $this->getAllowedPermissions()
				->values(),
			'channelCollection' => $this->getChannelCollection()
				->values(),
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
			$userCollection = new Collection(Types::string(), $data['members']);
			$this->setUserCollection($userCollection);

			$channelCollection = new Collection(Types::string(), $data['linkedChannels']);
			$this->setChannelCollection($channelCollection);

			$allowedPermissions = new Collection(Types::string(), $data['allowedPermissions']);
			$this->setAllowedPermissions($allowedPermissions);
			return;
		}

		$this->setCanHaveMembers((bool) $data['canHaveMembers']);

		if (($state = @unserialize($data['userCollection'])))
		{
			$userCollection = new Collection(Types::string());
			$userCollection->unserialize($data['userCollection']);
			$this->setUserCollection($userCollection);
		}
		else
			$this->setUserCollection(new Collection(Types::string(), $data['userCollection']));

		if (($state = @unserialize($data['allowedPermissions'])))
		{
			$allowedPermissions = new Collection(Types::string());
			$allowedPermissions->unserialize($data['allowedPermissions']);
			$this->setAllowedPermissions($allowedPermissions);
		}
		else
			$this->setAllowedPermissions(new Collection(Types::string(), $data['allowedPermissions']));

		if (($state = @unserialize($data['channelCollection'])))
		{
			$channelCollection = new Collection(Types::string());
			$channelCollection->unserialize($data['channelCollection']);
			$this->setChannelCollection($channelCollection);
		}
		else
			$this->setChannelCollection(new Collection(Types::string(), $data['channelCollection']));
	}
}