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

namespace WildPHP\Core\DataStorage;


use Flintstone\Flintstone;
use Flintstone\Formatter\JsonFormatter;
use WildPHP\Core\Configuration\Configuration;

class DataStorage
{
	/**
	 * @var Flintstone
	 */
	protected $flintstone;

	public function __construct($name)
	{
		$config = [
			'dir' => Configuration::get('rootdir')->getValue() . '/storage',
			'formatter' => new JsonFormatter()
		];
		$flintstone = new Flintstone($name, $config);
		$this->setFlintstone($flintstone);
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		return $this->getFlintstone()->get($key);
	}

	/**
	 * @param string $key
	 * @param $value
	 */
	public function set(string $key, $value)
	{
		$this->getFlintstone()->set($key, $value);
	}

	/**
	 * @param string $key
	 */
	public function delete(string $key)
	{
		$this->getFlintstone()->delete($key);
	}

	/**
	 * @return array
	 */
	public function getKeys(): array
	{
		return $this->getFlintstone()->getKeys();
	}

	public function flush()
	{
		$this->getFlintstone()->flush();
	}

	/**
	 * @return mixed
	 */
	public function getFlintstone()
	{
		return $this->flintstone;
	}

	/**
	 * @param mixed $flintstone
	 */
	public function setFlintstone($flintstone)
	{
		$this->flintstone = $flintstone;
	}

}