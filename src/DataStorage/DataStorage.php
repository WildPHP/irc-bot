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

namespace WildPHP\Core\DataStorage;


use Flintstone\Exception;
use Flintstone\Flintstone;
use Flintstone\Formatter\JsonFormatter;

class DataStorage extends Flintstone
{
	/**
	 * @var Flintstone
	 */
	protected $flintstone;

	/**
	 * DataStorage constructor.
	 *
	 * @param \Flintstone\Database|string $name
	 */
	public function __construct($name)
	{
		$config = [
			'dir' => WPHP_ROOT_DIR . '/storage',
			'formatter' => new JsonFormatter()
		];
		parent::__construct($name, $config);
	}
}