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

namespace WildPHP\Core\Commands;


class CommandHelp
{
    protected $pages = [];

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

    public function indexExists(int $index): int
    {
        return array_key_exists($index, $this->pages);
    }

    public function getPageCount(): int
    {
        return count($this->pages);
    }

    public function removePageAt(int $position): bool
    {
        if (!array_key_exists($position, $this->pages))
            return false;

        unset ($this->pages[$position]);
        $this->pages = array_values($this->pages);
        return true;
    }

    public function movePage(int $oldPosition, int $newPosition)
    {
        $out = array_splice($this->pages, $oldPosition, 1);
        array_splice($this->pages, $newPosition, 0, $out);
    }

    public function getFirstPage(): string
    {
        return end(array_reverse($this->pages));
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getPageAt(int $index): string
    {
        return $this->pages[$index];
    }
}