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

namespace WildPHP\CoreModules\Connection;

use Phergie\Irc\GeneratorInterface;
use Phergie\Irc\ParserInterface;
use WildPHP\BaseModuleInterface;

interface ConnectionModuleInterface extends BaseModuleInterface
{
	/**
	 * @return void
	 */
	public function setup();

	/**
	 * @return void
	 */
	public function create();

	/**
	 * @param string $data
	 * @return void
	 */
	public function parseData($data);

	/**
	 * @param IrcDataObject $data
	 * @return void
	 */
	public function pingPong(IrcDataObject $data);

	/**
	 * @return ParserInterface
	 */
	public function getParser();

	/**
	 * @return void
	 */
	public function sendInitialData();

	/**
	 * @param array $data
	 * @return void
	 */
	public function write($data);

	/**
	 * @return GeneratorInterface
	 */
	public function getGenerator();
}