<?php
	/**
	 * IRC Bot
	 *
	 * LICENSE: This source file is subject to Creative Commons Attribution
	 * 3.0 License that is available through the world-wide-web at the following URI:
	 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
	 * and use this script commercially/non-commercially. My only requirement is that
	 * you keep this header as an attribution to my work. Enjoy!
	 *
	 * @license http://creativecommons.org/licenses/by/3.0/
	 *
	 * @package IRCBot
	 * @author Super3 <admin@wildphp.org>
	 * @author Matej Velikonja <matej@velikonja.si>
	 */

	define('ROOT_DIR', __DIR__);
	define('CONFIG_FILE', '/config.neon');

	// Configure PHP
	//ini_set( 'display_errors', 'on' );

	// Make autoload working
	require 'Classes/Autoloader.php';
	spl_autoload_register( 'Autoloader::load' );

	if ( !file_exists(ROOT_DIR . CONFIG_FILE) || ($config = file_get_contents(ROOT_DIR . CONFIG_FILE)) === false ) {
		die('Could not read config file. Please Look at the Installaion Documentation.' . PHP_EOL);
	}

	try {
		$config = Nette\Neon\Neon::decode($config);
	} catch (Nette\Neon\Exception $e) {
		die('Configuration syntax error: ' . $e->getMessage() . PHP_EOL);
	}

	$timezone = ini_get('date.timezone');
	if (empty($timezone)) {
		if (empty($config['timezone']))
			$config['timezone'] = 'UTC';

		date_default_timezone_set($config['timezone']);
	}

	// Initialise the LogManager.
	$log = new Library\IRC\Log($config['log']);

	// Create the bot.
	$bot = new Library\IRC\Bot($config, $log);

	// Register the shutdown function.
	register_shutdown_function(array($bot, 'onShutdown'));

	// Add commands and listeners to the bot.
	$modules = array();

	if (!empty($config['commands']))
		$modules = $config['commands'];

	if (!empty($config['listeners']))
		$modules = array_merge($modules, $config['listeners']);

	foreach ($modules as $className => $args) {
		$reflector = new ReflectionClass($className);
		if(!isset($args))
			$args = array();

		$instance = $reflector->newInstanceArgs($args);
		if(array_key_exists($className, $config['commands'])) {
			$bot->commandManager->addCommand($instance);
		} else if(array_key_exists($className, $config['listeners'])) {
			$bot->listenerManager->addListener($instance);
		} else {
			$bot->log('Command/Listener loader found invalid class ( ' . $className . ' ). Skipping.', 'STARTUP');
		}
	}

	if (function_exists('setproctitle')) {
		$title = basename(__FILE__, '.php') . ' - ' . $config['nick'];
		setproctitle($title);
	}

	// And fire it up.
	$bot->run();

	// Nothing more possible, the bot runs until script ends.
