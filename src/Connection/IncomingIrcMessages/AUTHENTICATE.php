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

use WildPHP\Core\Connection\IncomingIrcMessage;

class AUTHENTICATE implements BaseMessage
{

    /**
     * @var string
     */
    protected $response = '';

    /**
     * @param IncomingIrcMessage $incomingIrcMessage
     *
     * @return AUTHENTICATE
     */
    public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage)
    {
        if ($incomingIrcMessage->getVerb() != 'AUTHENTICATE')
            throw new \InvalidArgumentException('Expected incoming AUTHENTICATE; got ' . $incomingIrcMessage->getVerb());
        $response = $incomingIrcMessage->getArgs()[0];

        $object = new self();
        $object->setResponse($response);
        return $object;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse(string $response)
    {
        $this->response = $response;
    }
}