<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\Replay;


use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use WildPHP\Core\Connection\ConnectionDetails;
use WildPHP\Core\Connection\ParsedIrcMessage;
use WildPHP\Core\Connection\Replay\Replies\ExactReply;
use WildPHP\Core\Connection\Replay\Replies\RegexReply;

class PrimitiveReplayServer
{
    /**
     * @var ReplayConnection
     */
    private $connection;

    public function __construct(
        EventEmitterInterface $eventEmitter,
        LoggerInterface $logger,
        ConnectionDetails $connectionDetails
    )
    {
        $connection = new ReplayConnection($eventEmitter, $logger, $connectionDetails);

        $structure = new ReplayStructure();
        $structure->addReply(new ExactReply("CAP LS", function () use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getAddress()} CAP * LS :extended-join userhost-in-names multi-prefix account-notify\r\n");
        }));

        $structure->addReply(new RegexReply("/^CAP REQ :(account-notify|extended-join|multi-prefix|userhost-in-names)$/", function (ParsedIrcMessage $msg) use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getAddress()} CAP * ACK :{$msg->args[2]}\r\n");
        }));
        $structure->addReply(new RegexReply("/^CAP REQ :.+$/", function (ParsedIrcMessage $msg) use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getAddress()} CAP * NAK :{$msg->args[2]}\r\n");
        }));

        $structure->addReply(new ExactReply('CAP END', function () use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getAddress()} 001 {$connectionDetails->getWantedNickname()} :Welcome to the ExampleNET IRC Network {$connectionDetails->getWantedNickname()}!{$connectionDetails->getUsername()}@{$connectionDetails->getHostname()}\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 002 {$connectionDetails->getWantedNickname()} :Your host is {$connectionDetails->getAddress()}, running version UnrealIRCd-5.0.5.1\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 003 {$connectionDetails->getWantedNickname()} :This server was created Fri May 29 09:07:59 2020\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 004 {$connectionDetails->getWantedNickname()} {$connectionDetails->getAddress()} UnrealIRCd-5.0.5.1 iowrsxzdHtIDZRqpWGTSB lvhopsmntikraqbeIHzMQNRTOVKDdGLPZSCcf\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 005 {$connectionDetails->getWantedNickname()} AWAYLEN=307 CASEMAPPING=ascii CHANLIMIT=#:10 CHANMODES=beI,kLf,lH,psmntirzMQNRTOVKDdGPZSCc CHANNELLEN=32 CHANTYPES=# CLIENTTAGDENY=*,-draft/typing,-typing DEAF=d ELIST=MNUCT EXCEPTS EXTBAN=~,ptmTSOcarnqjf HCN :are supported by this server\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 005 {$connectionDetails->getWantedNickname()} PREFIX=(qaohv)~&@%+ QUITLEN=307 SAFELIST SILENCE=15 STATUSMSG=~&@%+ TARGMAX=DCCALLOW:,ISON:,JOIN:,KICK:4,KILL:,LIST:,NAMES:1,NOTICE:1,PART:,PRIVMSG:4,SAJOIN:,SAPART:,TAGMSG:1,USERHOST:,USERIP:,WATCH:,WHOIS:1,WHOWAS:1 TOPICLEN=360 UHNAMES USERIP WALLCHOPS WATCH=128 WATCHOPTS=A :are supported by this server\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 005 {$connectionDetails->getWantedNickname()} WHOX :are supported by this server\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 396 {$connectionDetails->getWantedNickname()} hostname :is now your displayed host\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} NOTICE {$connectionDetails->getWantedNickname()} :*** You are connected to {$connectionDetails->getAddress()} with TLSv1.2-ECDHE-ECDSA-CHACHA20-POLY1305\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 251 {$connectionDetails->getWantedNickname()} :There are 1 users and 1 invisible on 1 servers\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 254 {$connectionDetails->getWantedNickname()} 1 :channels formed\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 255 {$connectionDetails->getWantedNickname()} :I have 2 clients and 0 servers\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 265 {$connectionDetails->getWantedNickname()} 2 4 :Current local users 2, max 4\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 266 {$connectionDetails->getWantedNickname()} 2 2 :Current global users 2, max 2\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 422 {$connectionDetails->getWantedNickname()} :MOTD File is missing\r\n");
            $connection->incomingData("{$connectionDetails->getWantedNickname()} MODE {$connectionDetails->getWantedNickname()} :+iwxz [] []\r\n");
        }));

        $structure->addReply(new RegexReply('/^JOIN [#a-zA-Z]+$/', function (ParsedIrcMessage $msg) use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getWantedNickname()}!{$connectionDetails->getWantedNickname()}@hostname JOIN {$msg->args[0]} * :Bot\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 353 {$connectionDetails->getWantedNickname()} = {$msg->args[0]} :{$connectionDetails->getWantedNickname()}!{$connectionDetails->getWantedNickname()}@{$connectionDetails->getHostname()}\r\n");
            $connection->incomingData(":{$connectionDetails->getAddress()} 366 {$connectionDetails->getWantedNickname()} {$msg->args[0]} :End of /NAMES list.\r\n");
        }));

        $structure->addReply(new RegexReply('/^WHO [#a-zA-Z]+ %nuhaf$/', function () use ($connection, $connectionDetails) {
            $connection->incomingData(":{$connectionDetails->getAddress()} 354 {$connectionDetails->getWantedNickname()} {$connectionDetails->getWantedNickname()} {$connectionDetails->getHostname()} {$connectionDetails->getWantedNickname()} Hs 0\r\n");
        }));
        $connection->setStructure($structure);

        $this->connection = $connection;
    }

    /**
     * @return ReplayConnection
     */
    public function getConnection(): ReplayConnection
    {
        return $this->connection;
    }
}