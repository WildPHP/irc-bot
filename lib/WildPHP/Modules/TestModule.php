<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 29/09/15
 * Time: 14:01
 */

namespace WildPHP\Modules;


use WildPHP\BaseModule;

class TestModule extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in', function ($data)
		{
			$this->getLogger()->info('<< ' . $data['message']);
		});
	}
}