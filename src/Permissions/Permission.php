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

use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Logger\Logger;

class Permission
{
	/**
	 * @var CriteriaCollection
	 */
	protected $criteriaCollection = null;

	/**
	 * @var string
	 */
	protected $name = '';

	public function __construct(string $name)
	{
		$this->setCriteriaCollection(new CriteriaCollection($name));
		$this->setName($name);
	}

	/**
	 * @param string $accountName
	 * @param string $channel
	 * @param array $modes
	 *
	 * @return bool
	 */
	public function allows(string $accountName, string $channel = '', array $modes)
	{
		Logger::debug('Evaluating permission...',
			[
				$accountName, $channel, $modes
			]);

		if ($accountName == Configuration::get('owner')->getValue())
			return true;

		if ($this->getCriteriaCollection()->getCollection()->count() == 0)
			return false;

		$result = $this->getCriteriaCollection()->getCollection()->every(
			function (PermissionCriteria $criteria) use ($accountName, $channel, $modes)
			{
				return $criteria->match($accountName, $channel, $modes);
			}
		);

		return $result;
	}

	/**
	 * @return CriteriaCollection
	 */
	public function getCriteriaCollection()
	{
		return $this->criteriaCollection;
	}

	/**
	 * @param CriteriaCollection $criteriaCollection
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