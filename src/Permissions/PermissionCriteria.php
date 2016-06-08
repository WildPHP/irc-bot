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

namespace WildPHP\Core\Permissions;

use WildPHP\Core\Logger\Logger;

class PermissionCriteria
{
	/**
	 * @var string
	 */
	protected $accountName = '';
	/**
	 * @var string
	 */
	protected $channel = '';
	/**
	 * @var string
	 */
	protected $mode = '';

	public function __construct(string $accountName = null, string $channel = null, string $mode = '')
	{
		$this->setAccountName($accountName);
		$this->setChannel($channel);
		$this->setMode($mode);
	}

	/**
	 * @param string $accountName
	 * @return bool
	 */
	public function matchAccountName(string $accountName): bool
	{
		if (empty($this->getAccountName()))
			return true;

		return $this->getAccountName() == $accountName;
	}

	/**
	 * @param string $channel
	 * @return bool
	 */
	public function matchChannel(string $channel): bool
	{
		if (empty($this->getChannel()))
			return true;

		return $this->getChannel() == $channel;
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	public function matchSingleMode(string $mode): bool
	{
		if (empty($this->getMode()))
			return true;

		return $this->getMode() == $mode;
	}

	/**
	 * @param array $modes
	 * @return bool
	 */
	public function matchMultipleModes(array $modes): bool
	{
		if (empty($this->getMode()))
			return true;

		return in_array($this->getMode(), $modes);
	}

	/**
	 * @param string $accountName
	 * @param string $channel
	 * @param array|string $modes
	 * @return bool
	 */
	public function match(string $accountName = '', string $channel = '', $modes = []): bool
	{
		Logger::debug('Matching permission criteria', [
			'expectedAccountName' => $this->getAccountName(),
			'acquiredAccountName' => $accountName,
			'expectedChannel' => $this->getChannel(),
			'acquiredChannel' => $channel,
			'expectedMode' => $this->getMode(),
			'acquiredMode' => $modes,
			'multipleModes' => is_array($modes)
		]);
		$accountNameMatched = $this->matchAccountName($accountName);
		$channelMatched = $this->matchChannel($channel);
		
		if (is_array($modes))
			$modeMatched = $this->matchMultipleModes($modes);
		else
			$modeMatched = $this->matchSingleMode($modes);
		
		return $accountNameMatched && $modeMatched && $channelMatched;
	}

	/**
	 * @return string
	 */
	public function getMode(): string
	{
		return $this->mode;
	}

	/**
	 * @param string $mode
	 */
	public function setMode(string $mode)
	{
		$this->mode = $mode;
	}

	/**
	 * @return string
	 */
	public function getChannel(): string
	{
		return $this->channel;
	}

	/**
	 * @param string $channel
	 */
	public function setChannel(string $channel)
	{
		$this->channel = $channel;
	}

	/**
	 * @return string
	 */
	public function getAccountName(): string
	{
		return $this->accountName;
	}

	/**
	 * @param string $accountName
	 */
	public function setAccountName(string $accountName)
	{
		$this->accountName = $accountName;
	}

}