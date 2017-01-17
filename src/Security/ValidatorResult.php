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

class ValidatorResult
{
	/**
	 * @var bool
	 */
	protected $result = false;

	/**
	 * @var string
	 */
	protected $reason = '';

	/**
	 * @return bool
	 */
	public function getResult(): bool
	{
		return $this->result;
	}

	/**
	 * @param bool $result
	 */
	public function setResult(bool $result)
	{
		$this->result = $result;
	}

	/**
	 * @return string
	 */
	public function getReason(): string
	{
		return $this->reason;
	}

	/**
	 * @param string $reason
	 */
	public function setReason(string $reason)
	{
		$this->reason = $reason;
	}
}