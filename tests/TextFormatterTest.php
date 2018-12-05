<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Observers\TextFormatter;

class TextFormatterTest extends TestCase
{
    public function testBold()
    {
        $string = 'Test string';
        $expectedString = "\x02" . $string . "\x02";

        $actual = TextFormatter::bold($string);

        static::assertSame($expectedString, $actual);
    }

    public function testUnderline()
    {
        $string = 'Test string';
        $expectedString = "\x1F" . $string . "\x1F";

        $actual = TextFormatter::underline($string);

        static::assertSame($expectedString, $actual);
    }

    public function testItalic()
    {
        $string = 'Test string';
        $expectedString = "\x09" . $string . "\x09";

        $actual = TextFormatter::italic($string);

        static::assertSame($expectedString, $actual);
    }

    public function testColor()
    {
        $string = 'Test string';
        $expectedString = "\x0306,04" . $string . "\x03";

        $actual = TextFormatter::color($string, 'purple', 'red');
        $actualNumeric = TextFormatter::color($string, '06', '04');

        static::assertSame($expectedString, $actual);
        static::assertSame($expectedString, $actualNumeric);
    }

	public function testCalculateStringColor()
	{
		$expected = 11;
		$string = 'Test';
		
		self::assertEquals($expected, TextFormatter::calculateStringColor($string));
    }

	public function testConsistentStringColor()
	{
		$expected = "\x0311" . 'Test' . "\x03";
		$string = 'Test';
		
		self::assertEquals($expected, TextFormatter::consistentStringColor($string));
		self::assertEquals('', TextFormatter::consistentStringColor(''));
    }

	public function testStripBold()
	{
		$expectedString = 'Test string';
		$string = "\x02" . $expectedString . "\x02";

		$actual = TextFormatter::stripBold($string);

		static::assertSame($expectedString, $actual);
	}

	public function testStripUnderline()
	{
		$expectedString = 'Test string';
		$string = "\x1F" . $expectedString . "\x1F";

		$actual = TextFormatter::stripUnderline($string);

		static::assertSame($expectedString, $actual);
	}

	public function testStripItalic()
	{
		$expectedString = 'Test string';
		$string = "\x09" . $expectedString . "\x09";

		$actual = TextFormatter::stripItalic($string);

		static::assertSame($expectedString, $actual);
	}

	public function testStripColor()
	{
		$expectedString = 'Test string';
		$string = "\x0306,04" . $expectedString . "\x03";

		$actual = TextFormatter::stripColor($string);

		static::assertSame($expectedString, $actual);
	}
}
