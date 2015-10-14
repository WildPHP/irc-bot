<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\CoreModules\Configuration;

class ConfigurationStorage
{
	private $config = [];

	/**
	 * Creates a storage for the following array.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @param string $key
	 * @return false|mixed False on failure; mixed on success.
	 */
	public function get($key)
	{
		$pieces = explode('.', $key);

		$lastPiece = $this->config;
		foreach ($pieces as $piece)
		{
			if (empty($lastPiece))
				return false;

			if (array_key_exists($piece, $lastPiece))
				$lastPiece = $lastPiece[$piece];
			else
				return false;
		}

		return $lastPiece;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value)
	{
		$pieces = explode('.', $key);

		$lastPiece =& $this->config;
		foreach ($pieces as $piece)
		{
			$lastPiece =& $lastPiece[$piece];
		}
		$lastPiece = $value;
	}

	/**
	 * @return array
	 */
	public function getAll()
	{
		return $this->config;
	}
}
