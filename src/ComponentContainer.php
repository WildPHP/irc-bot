<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core;

use React\EventLoop\LoopInterface;

class ComponentContainer
{
	/**
	 * @var LoopInterface
	 **/
	protected $loop = null;

	/**
	 * @var object[]
	 */
	protected $storedComponents = [];

	/**
	 * @param $object
	 */
	public function store($object)
	{
		$this->storedComponents[get_class($object)] = $object;
	}

	/**
	 * @param string $className
	 *
	 * @return object
	 */
	public function retrieve(string $className)
	{
		if (!array_key_exists($className, $this->storedComponents))
			throw new \InvalidArgumentException('Could not retrieve object from container: ' . $className);

		return $this->storedComponents[$className];
	}

	/**
	 * @return LoopInterface
	 */
	public function getLoop(): LoopInterface
	{
		return $this->loop;
	}

	/**
	 * @param LoopInterface $loop
	 */
	public function setLoop(LoopInterface $loop)
	{
		$this->loop = $loop;
	}
}