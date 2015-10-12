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

namespace WildPHP;

use Evenement\EventEmitter;
use React\EventLoop\Factory;
use WildPHP\Modules\ModuleProviders\DirectoryScanner;
use WildPHP\Modules\ModuleProxy;
use WildPHP\Traits\EventEmitterTrait;
use WildPHP\Traits\LoopTrait;
use WildPHP\Traits\ModuleProxyTrait;

/**
 * The main bot class. Creates a single bot instance.
 */
class Bot
{
	use EventEmitterTrait;
	use LoopTrait;
	use ModuleProxyTrait;

	/**
	 * This initialises the event emitter, loop, and module proxy objects.
	 */
	public function __construct()
	{
		$this->setLoop(Factory::create());

		$this->setEventEmitter(new EventEmitter());

		// Module proxy needs a bit of code.
		$moduleProxy = new ModuleProxy();
		$moduleProxy->setEventEmitter($this->getEventEmitter());
		$moduleProxy->setLoop($this->getLoop());

		$this->setModuleProxy($moduleProxy);
	}

	/**
	 * @param string[] $modules
	 */
	public function addModules(array $modules)
	{
		$this->getModuleProxy()->loadModules($modules);
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		$this->getModuleProxy()->initializeModules();
		$this->getLoop()->run();
	}
}
