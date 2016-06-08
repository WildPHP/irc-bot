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

class Permission
{
	/**
	 * @var Collection
	 */
	protected $criteriaCollection = null;

	/**
	 * @var string
	 */
	protected $name = '';

	public function __construct(string $name)
	{
		$this->setCriteriaCollection(new Collection(__NAMESPACE__ . '\PermissionCriteria'));
		$this->setName($name);
	}

	public function allows(string $accountName, string $channel = null, array $modes)
	{
		$result = $this->getCriteriaCollection()->every(
			function (PermissionCriteria $criteria) use ($accountName, $channel, $modes)
			{
				return $criteria->match($accountName, $channel, $modes);
			}
		);

		return $result;
	}

	/**
	 * @return Collection
	 */
	public function getCriteriaCollection()
	{
		return $this->criteriaCollection;
	}

	/**
	 * @param Collection $criteriaCollection
	 */
	public function setCriteriaCollection($criteriaCollection)
	{
		$this->criteriaCollection = $criteriaCollection;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
}