<?php
/**
 * Copyright (C) 2015 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CrEOF\Geo\Tests;

use CrEOF\Geo\Parser;

/**
 * Parser tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $input
     * @param mixed  $expected
     *
     * @dataProvider dataSourceGood
     */
    public function testGoodValues($input, $expected)
    {
        $parser = new Parser($input);

        $value = $parser->parse();

        $this->assertEquals($expected, $value);
    }

    /**
     * @param string $input
     * @param string $exception
     * @param string $message
     *
     * @dataProvider dataSourceBad
     */
    public function testBadValues($input, $exception, $message)
    {
        $this->setExpectedException($exception, $message);

        $parser = new Parser($input);

        $parser->parse();
    }

    /**
     * @return array[]
     */
    public function dataSourceGood()
    {
        return array(
            array('40', 40),
            array('-40', -40),
            array('40°', 40),
            array('-40°', -40),
            array('40° N', 40),
            array('40° S', -40),
            array('45.24', 45.24),
            array('45.24°', 45.24),
            array('+45.24°', 45.24),
            array('45.24° S', -45.24),
            array('40° 26\' 46" N', 40.446111111111),
            array('40:26S', -40.43333333333333),
            array('79:56:55W', -79.948611111111),
            array('40:26:46N', 40.446111111111),
            array('40° N 79° W', array(40, -79)),
            array('40 79', array(40, 79)),
            array('40° 79°', array(40, 79)),
            array('40, 79', array(40, 79)),
            array('40°, 79°', array(40, 79)),
            array('40° 26\' 46" N 79° 58\' 56" W', array(40.446111111111, -79.982222222222)),
            array('40° 26\' N 79° 58\' W', array(40.43333333333333, -79.966666666666669)),
            array('40.4738° N, 79.553° W', array(40.4738, -79.553)),
            array('40.4738° S, 79.553° W', array(-40.4738, -79.553)),
            array('40° 26.222\' N 79° 58.52\' E', array(40.437033333333, 79.975333333333)),
            array('40°26.222\'N 79°58.52\'E', array(40.437033333333, 79.975333333333)),
            array('40°26.222\' 79°58.52\'', array(40.437033333333, 79.975333333333)),
            array('40.222° -79.5852°', array(40.222, -79.5852)),
            array('40.222°, -79.5852°', array(40.222, -79.5852)),
            array('44°58\'53.9"N 93°19\'25.8"W', array(44.981638888888888, -93.32383333333334)),
            array('44°58\'53.9"N, 93°19\'25.8"W', array(44.981638888888888, -93.32383333333334)),
            array('79:56:55W 40:26:46N', array(-79.948611111111, 40.446111111111)),
            array('79:56:55 W, 40:26:46 N', array(-79.948611111111, 40.446111111111)),
            array('79°56′55″W, 40°26′46″N', array(-79.948611111111, 40.446111111111))
        );
    }

    /**
     * @return string[]
     */
    public function dataSourceBad()
    {
        return array(
            array('-40°N 45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 5: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "N" in value "-40°N 45°W"'),
            array('+40°N 45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 5: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "N" in value "+40°N 45°W"'),
            array('40°N +45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 6: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "+" in value "40°N +45°W"'),
            array('40°N -45W', 'UnexpectedValueException', '[Syntax Error] line 0, col 6: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "-" in value "40°N -45W"'),
            array('40N -45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 4: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "-" in value "40N -45°W"'),
            array('40N 45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 6: Error: Expected CrEOF\Geo\Lexer::T_CARDINAL_LON, got "°" in value "40N 45°W"'),
            array('40°N 45°S', 'UnexpectedValueException', '[Syntax Error] line 0, col 10: Error: Expected CrEOF\Geo\Lexer::T_CARDINAL_LON, got "S" in value "40°N 45°S"'),
            array('40°W 45°E', 'UnexpectedValueException', '[Syntax Error] line 0, col 10: Error: Expected CrEOF\Geo\Lexer::T_CARDINAL_LAT, got "E" in value "40°W 45°E"'),
            array('40° 45', 'UnexpectedValueException', '[Syntax Error] line 0, col -1: Error: Expected CrEOF\Geo\Lexer::T_APOSTROPHE, got end of string. in value "40° 45"'),
            array('40°, 45', 'UnexpectedValueException', '[Syntax Error] line 0, col -1: Error: Expected CrEOF\Geo\Lexer::T_DEGREE, got end of string. in value "40°, 45"'),
            array('40N 45', 'UnexpectedValueException', '[Syntax Error] line 0, col -1: Error: Expected CrEOF\Geo\Lexer::T_CARDINAL_LON, got end of string. in value "40N 45"'),
            array('40 45W', 'UnexpectedValueException', '[Syntax Error] line 0, col 5: Error: Expected end of string, got "W" in value "40 45W"'),
            array('-40.757° 45°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 14: Error: Expected end of string, got "W" in value "-40.757° 45°W"'),
            array('40.757°N -45.567°W', 'UnexpectedValueException', '[Syntax Error] line 0, col 10: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "-" in value "40.757°N -45.567°W"'),
            array('44°58\'53.9N 93°19\'25.8"W', 'UnexpectedValueException', '[Syntax Error] line 0, col 11: Error: Expected CrEOF\Geo\Lexer::T_QUOTE, got "N" in value "44°58\'53.9N 93°19\'25.8"W"'),
            array('40:26\'', 'UnexpectedValueException', '[Syntax Error] line 0, col 5: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "\'" in value "40:26\'"'),
            array('132.4432:', 'UnexpectedValueException', '[Syntax Error] line 0, col 8: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got ":" in value "132.4432:"'),
            array('55:34:22°', 'UnexpectedValueException', '[Syntax Error] line 0, col 8: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "°" in value "55:34:22°"'),
            array('55:34.22', 'UnexpectedValueException', '[Syntax Error] line 0, col 3: Error: Expected CrEOF\Geo\Lexer::T_INTEGER, got "34.22" in value "55:34.22"'),
            array('55#34.22', 'UnexpectedValueException', '[Syntax Error] line 0, col 2: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "#" in value "55#34.22"'),
            array('55 . 34.22', 'UnexpectedValueException', '[Syntax Error] line 0, col 3: Error: Expected CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT, got "." in value "55 . 34.22"'),
            array('200N', 'RangeException', '[Range Error] Error: Degrees out of range -90 to 90 in value "200N"'),
            array('55:200:32', 'RangeException', '[Range Error] Error: Minutes greater than 60 in value "55:200:32"'),
            array('55:20:99', 'RangeException', '[Range Error] Error: Seconds greater than 60 in value "55:20:99"'),
            array('55°70.99\'', 'RangeException', '[Range Error] Error: Minutes greater than 60 in value "55°70.99\'"')
        );
    }
}
