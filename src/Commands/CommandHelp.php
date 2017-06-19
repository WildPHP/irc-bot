<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
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