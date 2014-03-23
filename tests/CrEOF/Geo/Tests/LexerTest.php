<?php
/**
 * Copyright (C) 2014 Derek J. Lambert
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

use CrEOF\Geo\Lexer;

/**
 * Lexer tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $input
     * @param array  $tokens
     *
     * @dataProvider testDataSource
     */
    public function testLexer($input, array $tokens)
    {
        $lexer = new Lexer($input);

        $index = 0;

        while (null !== $actual = $lexer->peek()) {
            $this->assertEquals($tokens[$index++], $actual);
        }
    }

    /**
     * @return array[]
     */
    public function testDataSource()
    {
        return array (
            array(
                '40° 26\' 46" N',
                array(
                    array('value' => 40, 'type' => Lexer::T_INTEGER, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 2),
                    array('value' => 26, 'type' => Lexer::T_INTEGER, 'position' => 5),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 7),
                    array('value' => 46, 'type' => Lexer::T_INTEGER, 'position' => 9),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 11),
                    array('value' => 'N', 'type' => Lexer::T_CARDINAL_LAT, 'position' => 13)
                )
            ),
            array(
                '40° 26\' 46" N 79° 58\' 56" W',
                array(
                    array('value' => 40, 'type' => Lexer::T_INTEGER, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 2),
                    array('value' => 26, 'type' => Lexer::T_INTEGER, 'position' => 5),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 7),
                    array('value' => 46, 'type' => Lexer::T_INTEGER, 'position' => 9),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 11),
                    array('value' => 'N', 'type' => Lexer::T_CARDINAL_LAT, 'position' => 13),
                    array('value' => 79, 'type' => Lexer::T_INTEGER, 'position' => 15),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 17),
                    array('value' => 58, 'type' => Lexer::T_INTEGER, 'position' => 20),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 22),
                    array('value' => 56, 'type' => Lexer::T_INTEGER, 'position' => 24),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 26),
                    array('value' => 'W', 'type' => Lexer::T_CARDINAL_LONG, 'position' => 28)
                )
            ),
            array(
                '40°26\'46"N 79°58\'56"W',
                array(
                    array('value' => 40, 'type' => Lexer::T_INTEGER, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 2),
                    array('value' => 26, 'type' => Lexer::T_INTEGER, 'position' => 4),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 6),
                    array('value' => 46, 'type' => Lexer::T_INTEGER, 'position' => 7),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 9),
                    array('value' => 'N', 'type' => Lexer::T_CARDINAL_LAT, 'position' => 10),
                    array('value' => 79, 'type' => Lexer::T_INTEGER, 'position' => 12),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 14),
                    array('value' => 58, 'type' => Lexer::T_INTEGER, 'position' => 16),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 18),
                    array('value' => 56, 'type' => Lexer::T_INTEGER, 'position' => 19),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 21),
                    array('value' => 'W', 'type' => Lexer::T_CARDINAL_LONG, 'position' => 22)
                )
            ),
            array(
                '40°26\'46"N, 79°58\'56"W',
                array(
                    array('value' => 40, 'type' => Lexer::T_INTEGER, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 2),
                    array('value' => 26, 'type' => Lexer::T_INTEGER, 'position' => 4),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 6),
                    array('value' => 46, 'type' => Lexer::T_INTEGER, 'position' => 7),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 9),
                    array('value' => 'N', 'type' => Lexer::T_CARDINAL_LAT, 'position' => 10),
                    array('value' => ',', 'type' => Lexer::T_COMMA, 'position' => 11),
                    array('value' => 79, 'type' => Lexer::T_INTEGER, 'position' => 13),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 15),
                    array('value' => 58, 'type' => Lexer::T_INTEGER, 'position' => 17),
                    array('value' => '\'', 'type' => Lexer::T_APOSTROPHE, 'position' => 19),
                    array('value' => 56, 'type' => Lexer::T_INTEGER, 'position' => 20),
                    array('value' => '"', 'type' => Lexer::T_QUOTE, 'position' => 22),
                    array('value' => 'W', 'type' => Lexer::T_CARDINAL_LONG, 'position' => 23)
                )
            ),
            array(
                '40.4738° N, 79.553° W',
                array(
                    array('value' => 40.4738, 'type' => Lexer::T_FLOAT, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 7),
                    array('value' => 'N', 'type' => Lexer::T_CARDINAL_LAT, 'position' => 10),
                    array('value' => ',', 'type' => Lexer::T_COMMA, 'position' => 11),
                    array('value' => 79.553, 'type' => Lexer::T_FLOAT, 'position' => 13),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 19),
                    array('value' => 'W', 'type' => Lexer::T_CARDINAL_LONG, 'position' => 22)
                )
            ),
            array(
                '40.4738°, 79.553°',
                array(
                    array('value' => 40.4738, 'type' => Lexer::T_FLOAT, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 7),
                    array('value' => ',', 'type' => Lexer::T_COMMA, 'position' => 9),
                    array('value' => 79.553, 'type' => Lexer::T_FLOAT, 'position' => 11),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 17),
                )
            ),
            array(
                '40.4738° -79.553°',
                array(
                    array('value' => 40.4738, 'type' => Lexer::T_FLOAT, 'position' => 0),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 7),
                    array('value' => -79.553, 'type' => Lexer::T_FLOAT, 'position' => 10),
                    array('value' => '°', 'type' => Lexer::T_DEGREE, 'position' => 17),
                )
            )
        );
    }
}
