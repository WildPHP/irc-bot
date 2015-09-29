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

namespace WildPHP\Modules;

use WildPHP\BaseModule;

class ModulePool
{
    /**
     * @var string[BaseModule]
     */
    protected $pool = array();

    /**
     * @param BaseModule $module
     * @param string $key
     */
    public function add(BaseModule $module, $key = '')
    {
        if (empty($key))
            $key = $module->getShortName();

        if ($this->exists($module) || $this->existsByKey($key))
            throw new \RuntimeException('Module ' . $key . ' already exists in this module pool.');

        $this->pool[$key] = $module;
    }

    /**
     * @param BaseModule $module
     * @return bool
     */
    public function exists(BaseModule $module)
    {
        return in_array($module, $this->pool);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function existsByKey($key)
    {
        return array_key_exists($key, $this->pool);
    }

    /**
     * @param BaseModule $module
     */
    public function remove(BaseModule $module)
    {
        if (!$this->exists($module))
            throw new \RuntimeException('The module ' . $module->getFullyQualifiedName() . ' does not exist in this module pool.');

        unset($this->pool[$this->getKey($module)]);
    }

    /**
     * @param string $key
     */
    public function removeByKey($key)
    {
        if (!$this->existsByKey($key))
            throw new \RuntimeException('There is no module with key ' . $key . ' registered.');

        unset($this->pool[$key]);
    }

    /**
     * @param string $key
     * @return BaseModule
     */
    public function get($key)
    {
        if (!$this->existsByKey($key))
            throw new \RuntimeException('There is no module with key ' . $key . ' registered.');

        return $this->pool[$key];
    }

    /**
     * @param BaseModule $module
     * @return string
     */
    public function getKey($module)
    {
        if (!$this->exists($module))
            throw new \RuntimeException('The module ' . $module->getFullyQualifiedName() . ' does not exist in this module pool.');

        return array_search($module, $this->pool);
    }
}