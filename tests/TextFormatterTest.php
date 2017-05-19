<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 19-5-2017
 * Time: 21:29
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\TextFormatter;

class TextFormatterTest extends TestCase
{
    public function testBold()
    {
        $string = 'Test string';
        $expectedString = "\x02" . $string . "\x02";

        $actual = TextFormatter::bold($string);

        $this->assertSame($expectedString, $actual);
    }

    public function testUnderline()
    {
        $string = 'Test string';
        $expectedString = "\x1F" . $string . "\x1F";

        $actual = TextFormatter::underline($string);

        $this->assertSame($expectedString, $actual);
    }

    public function testItalic()
    {
        $string = 'Test string';
        $expectedString = "\x09" . $string . "\x09";

        $actual = TextFormatter::italic($string);

        $this->assertSame($expectedString, $actual);
    }

    public function testColor()
    {
        $string = 'Test string';
        $expectedString = "\x0306,04" . $string . "\x03";

        $actual = TextFormatter::color($string, 'purple', 'red');
        $actualNumeric = TextFormatter::color($string, '06', '04');

        $this->assertSame($expectedString, $actual);
        $this->assertSame($expectedString, $actualNumeric);
    }
}
