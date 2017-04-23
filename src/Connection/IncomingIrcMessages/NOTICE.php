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

namespace WildPHP\Core\Connection\IncomingIrcMessages;


use WildPHP\Core\Channels\GlobalChannelCollection;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;
use WildPHP\Core\Users\GlobalUserCollection;

class NOTICE extends PRIVMSG
{
	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return NOTICE
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage)
	{
		if ($incomingIrcMessage->getVerb() != 'NOTICE')
			throw new \InvalidArgumentException('Expected incoming NOTICE; got ' . $incomingIrcMessage->getVerb());

		$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		$channel = $incomingIrcMessage->getArgs()[0];
		$user = GlobalUserCollection::getUserFromIncomingIrcMessage($incomingIrcMessage);

		if (!$user)
			throw new \ErrorException('Could not find user in collection; state mismatch!');

		if (GlobalChannelCollection::getChannelCollection()->channelExistsByName($channel))
			$channel = GlobalChannelCollection::getChannelCollection()->getChannelByName($channel);

		// It's most likely a private conversation.
		else
			$channel = GlobalChannelCollection::getChannelCollection()->createFakeConversationChannel($user);
		$message = $incomingIrcMessage->getArgs()[1];

		$object = new self();

		$object->setPrefix($prefix);
		$object->setUser($user);
		$object->setChannel($channel);
		$object->setMessage($message);

		return $object;
	}
}