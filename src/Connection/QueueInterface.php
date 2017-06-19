<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;

use WildPHP\Core\Connection\IRCMessages\SendableMessage;

interface QueueInterface
{
	/**
	 * @param SendableMessage $command
	 *
	 * @return void
	 */
	public function insertMessage(SendableMessage $command);

	/**
	 * @param SendableMessage $command
	 *
	 * @return void
	 */
	public function removeMessage(SendableMessage $command);

	/**
	 * @param int $index
	 *
	 * @return void
	 */
	public function removeMessageByIndex(int $index);

	/**
	 * @param QueueItem $item
	 *
	 * @return void
	 */
	public function scheduleItem(QueueItem $item);

	/**
	 * @return QueueItem[]
	 */
	public function flush(): array;

	/**
	 * @param string $nickname
	 *
	 * @return void
	 */
	public function nick(string $nickname);

	/**
	 * @param string $username
	 * @param string $hostname
	 * @param string $servername
	 * @param string $realname
	 *
	 * @return void
	 */
	public function user(string $username, string $hostname, string $servername, string $realname);

	/**
	 * @param string $channel
	 * @param string $message
	 *
	 * @return void
	 */
	public function privmsg(string $channel, string $message);

	/**
	 * @param string $server
	 *
	 * @return void
	 */
	public function pong(string $server);

	/**
	 * @param string|array $channel
	 * @param string|array $key
	 *
	 * @return void
	 */
	public function join($channel, $key = '');

	/**
	 * @param array|string $channel
	 *
	 * @return void
	 */
	public function part($channel);
}