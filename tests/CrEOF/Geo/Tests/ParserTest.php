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

namespace CrEOF\Spatial\Tests\Unit\DBAL\Types;

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
     * @param array  $expected
     *
     * @dataProvider testDataSource
     */
    public function testOne($input, array $expected)
    {
        $parser = new Parser($input);

        $value = $parser->parse();

        $this->assertEquals($expected, $value);
    }

    public function testDataSource()
    {
        return array(
            array(
                '40° 26\' 46" N 79° 58\' 56" W',
                array(40.446111111111, -79.982222222222)
            ),
            array(
                '40.4738° N, 79.553° W',
                array(40.4738, -79.553)
            ),
            array(
                '40.4738° S, 79.553° W',
                array(-40.4738, -79.553)
            ),
            array(
                '40° 26.222\' N 79° 58.52\' E',
                array(40.437033333333, 79.975333333333)
            ),
            array(
                '40°26.222\'N 79°58.52\'E',
                array(40.437033333333, 79.975333333333)
            ),
            array(
                '40°26.222\' 79°58.52\'',
                array(40.437033333333, 79.975333333333)
            ),
            array(
                '40.222° -79.5852°',
                array(40.222, -79.5852)
            ),
            array(
                '40.222°, -79.5852°',
                array(40.222, -79.5852)
            ),
        );
    }
}
