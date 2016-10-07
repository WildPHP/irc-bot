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

namespace WildPHP\Core\Connection;


use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Connection\Commands\Authenticate;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class SASL
{
    /**
     * @var bool
     */
    protected static $hasCompleted = false;
    /**
     * @var bool|string
     */
    protected static $errorReason = false;
    /**
     * @var bool
     */
    protected static $isSuccessful = false;

    /**
     * @var array
     */
    protected static $successCodes = [
        '900' => 'RPL_LOGGEDIN',
        '901' => 'RPL_LOGGEDOUT',
        '903' => 'RPL_SASLSUCCESS',
        '908' => 'RPL_SASLMECHS'
    ];

    /**
     * @var array
     */
    protected static $errorCodes = [
        '902' => 'ERR_NICKLOCKED',
        '904' => 'ERR_SASLFAIL',
        '905' => 'ERR_SASLTOOLONG',
        '906' => 'ERR_SASLABORTED',
        '907' => 'ERR_SASLALREADY'
    ];

    public static function initialize(Queue $queue)
    {
        if (!Configuration::get('sasl') || !Configuration::get('sasl.username') || !Configuration::get('sasl.password'))
        {
            Logger::info('SASL not initialized because no credentials were provided.');
            EventEmitter::emit('irc.sasl.error', [[], $queue]);
            return;
        }
        EventEmitter::on('irc.cap.acknowledged', __NAMESPACE__ . '\\SASL::sendAuthenticationMechanism');
        EventEmitter::on('irc.line.in.authenticate', __NAMESPACE__ . '\\SASL::sendCredentials');
        CapabilityHandler::requestCapability('sasl');

        // Map all numeric SASL responses to either the success or error handler:
        foreach (self::$successCodes as $code => $reason)
        {
            EventEmitter::on('irc.line.in.' . $code, __NAMESPACE__ . '\\SASL::handlePositiveResponse');
        }

        foreach (self::$errorCodes as $code => $reason)
        {
            EventEmitter::on('irc.line.in.' . $code, __NAMESPACE__ . '\\SASL::handleNegativeResponse');
        }

        Logger::debug('[SASL] Capability requested, awaiting server response.');
    }

    /**
     * @param array $acknowledgedCapabilities
     * @param Queue $queue
     */
    public static function sendAuthenticationMechanism(array $acknowledgedCapabilities, Queue $queue)
    {
        if (!in_array('sasl', $acknowledgedCapabilities))
            return;

        $queue->insertMessage(new Authenticate('PLAIN'));
        Logger::debug('[SASL] Authentication mechanism requested, awaiting server response.');
    }

    /**
     * @param string $username
     * @param string $password
     * @return string
     */
    protected static function generateCredentialString(string $username, string $password)
    {
        return base64_encode($username . "\0" . $username . "\0" . $password);
    }

    /**
     * @param IncomingIrcMessage $message
     * @param Queue $queue
     */
    public static function sendCredentials(IncomingIrcMessage $message, Queue $queue)
    {
        $message = $message->specialize();

        if ($message->getResponse() != '+')
            return;

        $username = Configuration::get('sasl.username')->getValue();
        $password = Configuration::get('sasl.password')->getValue();
        $credentials = self::generateCredentialString($username, $password);
        $queue->insertMessage(new Authenticate($credentials));
        Logger::debug('[SASL] Sent authentication details, awaiting response from server.');
    }

    /**
     * @param IncomingIrcMessage $message
     * @param Queue $queue
     */
    public static function handlePositiveResponse(IncomingIrcMessage $message, Queue $queue)
    {
        $code = $message->getVerb();

        self::setErrorReason(false);
        self::setHasCompleted(true);
        self::setIsSuccessful(true);

        if ($code != '903')
            return;

        // This event has to fit on the events used in CapabilityHandler.
        Logger::info('[SASL] Authentication successful!');
        EventEmitter::emit('irc.sasl.complete', [[], $queue]);
    }

    /**
     * @param IncomingIrcMessage $message
     * @param Queue $queue
     */
    public static function handleNegativeResponse(IncomingIrcMessage $message, Queue $queue)
    {
        $code = $message->getVerb();
        $reason = self::$errorCodes[$code];

        self::setErrorReason($reason);
        self::setHasCompleted(true);
        self::setIsSuccessful(false);

        // This event has to fit on the events used in CapabilityHandler.
        Logger::warning('[SASL] Authentication was NOT successful. Continuing unauthenticated.');
        EventEmitter::emit('irc.sasl.error', [[], $queue]);
    }

    /**
     * @param string|false $reason
     */
    public static function setErrorReason($reason)
    {
        self::$errorReason = $reason;
    }

    /**
     * @param boolean $hasCompleted
     */
    public static function setHasCompleted(bool $hasCompleted)
    {
        self::$hasCompleted = $hasCompleted;
    }

    /**
     * @param boolean $isSuccessful
     */
    public static function setIsSuccessful(bool $isSuccessful)
    {
        self::$isSuccessful = $isSuccessful;
    }

    /**
     * @return bool
     */
    public static function hasCompleted(): bool
    {
        return self::$hasCompleted;
    }

    /**
     * @return bool
     */
    public static function isSuccessful(): bool
    {
        return self::$isSuccessful;
    }

    /**
     * @return bool|string
     */
    public static function hasEncounteredError()
    {
        return self::$errorReason;
    }
}