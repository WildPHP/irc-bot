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
	 * Loads configuration, sets up a connection and loads modules.
	 *
	 * @param string $configFile
	 */
	public function __construct($configFile = WPHP_CONFIG)
	{
		$this->setLoop(Factory::create());

		$this->setEventEmitter(new EventEmitter());

		// Module proxy needs a bit of code.
		$moduleProxy = new ModuleProxy();
		$moduleProxy->setEventEmitter($this->getEventEmitter());

		$dirScanner = new DirectoryScanner(dirname(__FILE__) . '/CoreModules');
		$moduleProxy->loadModules($dirScanner->getValidModules());
		$moduleProxy->initializeModules();

		$this->setModuleProxy($moduleProxy);
	}

	/**
	 * Starts the bot's main loop.
	 */
	public function start()
	{
		$this->getLoop()->run();
	}
}
