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

namespace WildPHP\Core\Permissions;


use Collections\Collection;
use WildPHP\Core\DataStorage\DataStorageFactory;
use WildPHP\Core\Logger\Logger;

class CriteriaCollection
{
	/**
	 * @var Collection
	 */
	protected $collection;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->setName($name);
		$this->collection = new Collection(__NAMESPACE__ . '\PermissionCriteria');
		$this->loadExistingCollection();
		register_shutdown_function([$this, '__destruct']);
	}

	public function __destruct()
	{
		$array = [];
		$this->getCollection()->every(
			function (PermissionCriteria $criteria) use (&$array)
			{
				$array[] = [
					'accountName' => $criteria->getAccountName(),
					'channel' => $criteria->getChannel(),
					'mode' => $criteria->getMode()
				];
			}
		);

		$dataStorage = DataStorageFactory::getStorage('permissionCriteria');

		$dataStorage->set($this->getName(), $array);
	}

	public function add(PermissionCriteria $criteria)
	{
		$this->getCollection()->add($criteria);
	}

	/**
	 * @param string $accountName
	 * @param string $channel
	 * @param string $mode
	 * @return int
	 */
	public function removeEvery(string $accountName = '', string $channel = '', string $mode = ''): int
	{
		$numRemoved = $this->getCollection()->removeAll(
			function (PermissionCriteria $criteria) use ($accountName, $channel, $mode)
			{
				if ($criteria->getAccountName() == $accountName &&
					$criteria->getChannel() == $channel && $criteria->getMode() == $mode)
					return true;

				return false;
			}
		);

		return $numRemoved;
	}

	/**
	 * @return Collection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * @param Collection $collection
	 */
	public function setCollection($collection)
	{
		$this->collection = $collection;
	}

	public function loadExistingCollection()
	{
		$dataStorage = DataStorageFactory::getStorage('permissionCriteria');
		
		if (!in_array($this->getName(), $dataStorage->getKeys()))
			return;
		
		$criteria = $dataStorage->get($this->getName());
		foreach ($criteria as $item)
		{
			if (!is_array($item) || !array_key_exists('mode', $item) ||
				!array_key_exists('accountName', $item) || !array_key_exists('channel', $item))
				continue;

			$accountName = $item['accountName'];
			$channel = $item['channel'];
			$mode = $item['mode'];
			$this->collection->add(new PermissionCriteria($accountName, $channel, $mode));
			Logger::debug('Inserted new permission criteria', [
				'permission' => $this->getName(),
				'accountName' => $accountName,
				'channel' => $channel,
				'mode' => $mode
			]);
		}
	}
}