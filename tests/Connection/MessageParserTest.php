<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Core\Connection;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\MessageParser;

class MessageParserTest extends TestCase
{

    public function testParseLine()
    {
        $line = ':nick!ident@host.com PRIVMSG me :Hello there';

        $parsed = MessageParser::parseLine($line);

        self::assertEquals('nick!ident@host.com', $parsed->prefix);
        self::assertEquals('PRIVMSG', $parsed->verb);
        self::assertEquals(
            [
                'PRIVMSG',
                'me',
                'Hello there'
            ],
            $parsed->args
        );
    }

    public function testParseLineWithTags()
    {
        $line = '@aaa=bbb;ccc;example.com/ddd=eee :nick!ident@host.com PRIVMSG me :Hello there';

        $parsed = MessageParser::parseLine($line);

        self::assertEquals('nick!ident@host.com', $parsed->prefix);
        self::assertEquals('PRIVMSG', $parsed->verb);
        self::assertEquals(
            [
                'PRIVMSG',
                'me',
                'Hello there'
            ],
            $parsed->args
        );
        self::assertEquals(
            [
                'aaa' => 'bbb',
                'ccc' => true,
                'example.com/ddd' => 'eee'
            ],
            $parsed->tags
        );
    }
}
