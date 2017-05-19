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

namespace WildPHP\Core\Commands;


class CommandHelp
{
	/**
	 * @var array
	 */
	protected $pages = [];

	/**
	 * @param string $contents
	 *
	 * @return int
	 */
	public function addPage(string $contents): int
	{
		if (in_array($contents, $this->pages))
			return -1;

		$this->pages[] = $contents;
		$this->pages = array_values($this->pages);
		$pages = $this->pages;
		$keys = array_keys($pages);

		return end($keys);
	}

	/**
	 * @param int $index
	 *
	 * @return int
	 */
	public function indexExists(int $index): int
	{
		return array_key_exists($index, $this->pages);
	}

	/**
	 * @return int
	 */
	public function getPageCount(): int
	{
		return count($this->pages);
	}

	/**
	 * @param int $position
	 *
	 * @return bool
	 */
	public function removePageAt(int $position): bool
	{
		if (!array_key_exists($position, $this->pages))
			return false;

		unset ($this->pages[$position]);
		$this->pages = array_values($this->pages);

		return true;
	}

	/**
	 * @param int $oldPosition
	 * @param int $newPosition
	 */
	public function movePage(int $oldPosition, int $newPosition)
	{
		$out = array_splice($this->pages, $oldPosition, 1);
		array_splice($this->pages, $newPosition, 0, $out);
	}

	/**
	 * @return string
	 */
	public function getFirstPage(): string
	{
		$reverse = array_reverse($this->pages);

		return end($reverse);
	}

	/**
	 * @return array
	 */
	public function getPages(): array
	{
		return $this->pages;
	}

	/**
	 * @param int $index
	 *
	 * @return string
	 */
	public function getPageAt(int $index): string
	{
		return $this->pages[$index];
	}
}