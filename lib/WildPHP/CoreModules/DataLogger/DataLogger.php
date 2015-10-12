<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 29/09/15
 * Time: 14:01
 */


namespace WildPHP\CoreModules;


use WildPHP\BaseModule;

class DataLogger extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in', function ($data)
		{
			//$this->getLogger()->info('<< ' . $data['message']);
		});

		$this->getEventEmitter()->on('wildphp.init.after', function()
		{
			$configurationExists = $this->getModulePool()->existsByKey('Configuration');

			if (!$configurationExists)
			{
				echo 'Uhm, I am missing my configuration...' . PHP_EOL;
				return;
			}

			$configuration = $this->getModulePool()->get('Configuration');

			$server = $configuration->get('server');
			var_dump($server);
		});
	}
}