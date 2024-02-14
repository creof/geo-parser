<?php
/**
 * Copyright (C) 2016 Derek J. Lambert
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

namespace CrEOF\Geo\String\Tests;

use CrEOF\Geo\String\Lexer;
use Doctrine\Common\Lexer\Token;
use PHPUnit\Framework\TestCase;

/**
 * Lexer tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class LexerTest extends TestCase
{
    /**
     * @param string $input
     * @param array  $expectedTokens
     *
     * @dataProvider tokenDataSource
     */
    public function testLexer($input, array $expectedTokens)
    {
        $lexer = new Lexer($input);
        $index = 0;

        while (null !== $actual = $lexer->peek()) {
            $this->assertEquals($expectedTokens[$index++], $actual);
        }
    }

    public function testReusedLexer()
    {
        $lexer = new Lexer();

        foreach ($this->tokenDataSource() as $data) {
            $input          = $data['input'];
            $expectedTokens = $data['expectedTokens'];
            $index          = 0;

            $lexer->setInput($input);

            while (null !== $actual = $lexer->peek()) {
                $this->assertEquals($expectedTokens[$index++], $actual);
            }
        }
    }

    /**
     * @return array[]
     */
    public function tokenDataSource()
    {
        return array (
            array(
                'input'          => '15',
                'expectedTokens' => array(
                    new Token(15, Lexer::T_INTEGER, 0),
                )
            ),
            array(
                'input'          => '1E5',
                'expectedTokens' => array(
                    new Token(100000, Lexer::T_FLOAT, 0),
                )
            ),
            array(
                'input'          => '1e5',
                'expectedTokens' => array(
                    new Token(100000, Lexer::T_FLOAT, 0),
                )
            ),
            array(
                'input'          => '1.5E5',
                'expectedTokens' => array(
                    new Token(150000, Lexer::T_FLOAT, 0),
                )
            ),
            array(
                'input'          => '1E-5',
                'expectedTokens' => array(
                    new Token('0.00001', Lexer::T_FLOAT, 0),
                )
            ),
            array(
                'input'          => '40° 26\' 46" N',
                'expectedTokens' => array(
                    new Token(40, Lexer::T_INTEGER, 0),
                    new Token('°', Lexer::T_DEGREE, 2),
                    new Token(26, Lexer::T_INTEGER, 5),
                    new Token('\'', Lexer::T_APOSTROPHE, 7),
                    new Token(46, Lexer::T_INTEGER, 9),
                    new Token('"', Lexer::T_QUOTE, 11),
                    new Token('N', Lexer::T_CARDINAL_LAT, 13),
                )
            ),
            array(
                'input'          => '40° 26\' 46" N 79° 58\' 56" W',
                'expectedTokens' => array(
                    new Token(40, Lexer::T_INTEGER, 0),
                    new Token('°', Lexer::T_DEGREE, 2),
                    new Token(26, Lexer::T_INTEGER, 5),
                    new Token('\'', Lexer::T_APOSTROPHE, 7),
                    new Token(46, Lexer::T_INTEGER, 9),
                    new Token('"', Lexer::T_QUOTE, 11),
                    new Token('N', Lexer::T_CARDINAL_LAT, 13),
                    new Token(79, Lexer::T_INTEGER, 15),
                    new Token('°', Lexer::T_DEGREE, 17),
                    new Token(58, Lexer::T_INTEGER, 20),
                    new Token('\'', Lexer::T_APOSTROPHE, 22),
                    new Token(56, Lexer::T_INTEGER, 24),
                    new Token('"', Lexer::T_QUOTE, 26),
                    new Token('W', Lexer::T_CARDINAL_LON, 28),
                )
            ),
            array(
                'input'          => '40°26\'46"N 79°58\'56"W',
                'expectedTokens' => array(
                    new Token(40, Lexer::T_INTEGER, 0),
                    new Token('°', Lexer::T_DEGREE, 2),
                    new Token(26, Lexer::T_INTEGER, 4),
                    new Token('\'', Lexer::T_APOSTROPHE, 6),
                    new Token(46, Lexer::T_INTEGER, 7),
                    new Token('"', Lexer::T_QUOTE, 9),
                    new Token('N', Lexer::T_CARDINAL_LAT, 10),
                    new Token(79, Lexer::T_INTEGER, 12),
                    new Token('°', Lexer::T_DEGREE, 14),
                    new Token(58, Lexer::T_INTEGER, 16),
                    new Token('\'', Lexer::T_APOSTROPHE, 18),
                    new Token(56, Lexer::T_INTEGER, 19),
                    new Token('"', Lexer::T_QUOTE, 21),
                    new Token('W', Lexer::T_CARDINAL_LON, 22),
                )
            ),
            array(
                'input'          => '40°26\'46"N, 79°58\'56"W',
                'expectedTokens' => array(
                    new Token(40, Lexer::T_INTEGER, 0),
                    new Token('°', Lexer::T_DEGREE, 2),
                    new Token(26, Lexer::T_INTEGER, 4),
                    new Token('\'', Lexer::T_APOSTROPHE, 6),
                    new Token(46, Lexer::T_INTEGER, 7),
                    new Token('"', Lexer::T_QUOTE, 9),
                    new Token('N', Lexer::T_CARDINAL_LAT, 10),
                    new Token(',', Lexer::T_COMMA, 11),
                    new Token(79, Lexer::T_INTEGER, 13),
                    new Token('°', Lexer::T_DEGREE, 15),
                    new Token(58, Lexer::T_INTEGER, 17),
                    new Token('\'', Lexer::T_APOSTROPHE, 19),
                    new Token(56, Lexer::T_INTEGER, 20),
                    new Token('"', Lexer::T_QUOTE, 22),
                    new Token('W', Lexer::T_CARDINAL_LON, 23)
                )
            ),
            array(
                'input'          => '40.4738° N, 79.553° W',
                'expectedTokens' => array(
                    new Token('40.4738', Lexer::T_FLOAT, 0),
                    new Token('°', Lexer::T_DEGREE, 7),
                    new Token('N', Lexer::T_CARDINAL_LAT, 10),
                    new Token(',', Lexer::T_COMMA, 11),
                    new Token('79.553', Lexer::T_FLOAT, 13),
                    new Token('°', Lexer::T_DEGREE, 19),
                    new Token('W', Lexer::T_CARDINAL_LON, 22)
                )
            ),
            array(
                'input'          => '40.4738°, 79.553°',
                'expectedTokens' => array(
                    new Token('40.4738', Lexer::T_FLOAT, 0),
                    new Token('°', Lexer::T_DEGREE, 7),
                    new Token(',', Lexer::T_COMMA, 9),
                    new Token('79.553', Lexer::T_FLOAT, 11),
                    new Token('°', Lexer::T_DEGREE, 17),
                )
            ),
            array(
                'input'          => '40.4738° -79.553°',
                'expectedTokens' => array(
                    new Token('40.4738', Lexer::T_FLOAT, 0),
                    new Token('°', Lexer::T_DEGREE, 7),
                    new Token('-', Lexer::T_MINUS, 10),
                    new Token('79.553', Lexer::T_FLOAT, 11),
                    new Token('°', Lexer::T_DEGREE, 17),
                )
            ),
            array(
                'input'          => "40.4738° \t -79.553°",
                'expectedTokens' => array(
                    new Token('40.4738', Lexer::T_FLOAT, 0),
                    new Token('°', Lexer::T_DEGREE, 7),
                    new Token('-', Lexer::T_MINUS, 12),
                    new Token('79.553', Lexer::T_FLOAT, 13),
                    new Token('°', Lexer::T_DEGREE, 19),
                )
            )
        );
    }
}
