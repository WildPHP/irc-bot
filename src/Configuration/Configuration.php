<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;

use ValidationClosures\Types;
use ValidationClosures\Utils;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Configuration extends Collection implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * @var ConfigurationBackendInterface
	 */
	protected $backend = null;

	/**
	 * Configuration constructor.
	 *
	 * @param ConfigurationBackendInterface $configurationBackend
	 */
	public function __construct(ConfigurationBackendInterface $configurationBackend)
	{
		$this->backend = $configurationBackend;

		// Accept any type, except objects.
		parent::__construct(Utils::invert(Types::object()), $configurationBackend->getAllEntries());
	}

	/**
	 * @return ConfigurationBackendInterface
	 */
	public function getBackend(): ConfigurationBackendInterface
	{
		return $this->backend;
	}
}