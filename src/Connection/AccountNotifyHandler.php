<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\Channels\Channel;
use WildPHP\Core\Channels\ChannelCollection;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\ACCOUNT;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Modules\BaseModule;

class AccountNotifyHandler extends BaseModule
{
	use ContainerTrait;

	public function __construct(ComponentContainer $container)
	{
		EventEmitter::fromContainer($container)->on('irc.line.in.account', [$this, 'updateUserIrcAccount']);
		$this->setContainer($container);
	}

	/**
	 * @param ACCOUNT $ircMessage
	 * @param Queue $queue
	 */
	public function updateUserIrcAccount(ACCOUNT $ircMessage, Queue $queue)
	{
		$channels = ChannelCollection::fromContainer($this->getContainer());
		$nickname = $ircMessage->getPrefix()->getNickname();

		/** @var Channel $channel */
		foreach ($channels as $channel)
		{
			if (!($user = $channel->getUserCollection()->findByNickname($nickname)))
				continue;

			$user->setIrcAccount($ircMessage->getAccountName());
		}
	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return WPHP_VERSION;
	}
}