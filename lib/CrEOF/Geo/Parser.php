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

namespace CrEOF\Geo;

/**
 * Parse geographic coordinates
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class Parser
{
    /**
     * @var string
     */
    private $input;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var int
     */
    private $cardinal;

    /**
     * Constructor
     *
     * Setup up instance properties
     *
     * @param string $input
     */
    public function __construct($input)
    {
        // Save input string for any syntax error
        $this->input = $input;
        // Create new Lexer and tokenize input string
        $this->lexer = new Lexer($input);
    }

    /**
     * Parse input string
     *
     * @return float|int|array
     */
    public function parse()
    {
        // Move Lexer to first token
        $this->lexer->moveNext();

        // Parse and return value
        return $this->point();
    }

    /**
     * @return float|int|array
     */
    protected function point()
    {
        // Get first coordinate value
        $x = $this->coordinate();

        // If no additional tokens return single coordinate
        if (null === $this->lexer->lookahead) {
            return $x;
        }

        // Coordinate pairs may be separated by a comma
        if ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
        }

        // Get second coordinate value
        $y = $this->coordinate();

        // Return coordinate array
        return array($x, $y);
    }

    /**
     * @return float|int
     */
    protected function coordinate()
    {
        // Get coordinate value
        $coordinate = $this->degrees();

        // Get sign if cardinal direction requirement defined by first coordinate
        if ($this->cardinal > 0) {
            return $coordinate * $this->cardinal();
        }

        // Get sign if this is first coordinate and cardinal direction is present
        if (null === $this->cardinal && $this->lexer->isNextTokenAny(array(Lexer::T_CARDINAL_LAT, Lexer::T_CARDINAL_LONG))) {
            return $coordinate * $this->cardinal();
        }

        // Remember there was no cardinal direction on first coordinate
        $this->cardinal = -1;

        // Return value
        return $coordinate;
    }

    /**
     * Match and return degree value
     *
     * @return float|int
     */
    protected function degrees()
    {
        // If degrees is a float there will be no minutes or seconds
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            // Get degree value
            $degrees = $this->number();

            // Degree float values may be followed by degree symbol
            if ($this->lexer->isNextToken(Lexer::T_DEGREE)) {
                $this->match(Lexer::T_DEGREE);
            }

            // Return value
            return $degrees;
        }

        // If degrees isn't a float it must be an integer
        $degrees = $this->number();

        // If integer is not followed by a degree symbol this value is complete
        if ( ! $this->lexer->isNextToken(Lexer::T_DEGREE)) {
            return $degrees;
        }

        // Match degree symbol
        $this->match(Lexer::T_DEGREE);

        // If next token is a number followed by degree symbol this value is complete
        if ($this->lexer->isNextTokenAny(array(Lexer::T_INTEGER, Lexer::T_FLOAT)) && Lexer::T_DEGREE === $this->lexer->glimpse()['type']) {
            return $degrees;
        }

        // Add minutes to value
        $degrees += $this->minutes();

        // Return value
        return $degrees;
    }

    /**
     * Match and return minutes value
     *
     * @return float|int
     */
    protected function minutes()
    {
        // If minutes is a float there will be no seconds
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            // Get fractional minutes
            $minutes = $this->number() / 60;

            // Match minutes symbol
            $this->match(Lexer::T_APOSTROPHE);

            // return value
            return $minutes;
        }

        // If minutes is an integer parse value
        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            // Get fractional minutes
            $minutes = $this->number() / 60;

            // Match minutes symbol
            $this->match(Lexer::T_APOSTROPHE);

            // Add seconds to value
            $minutes += $this->seconds();

            // Return value
            return $minutes;
        }

        // No minutes were present so return 0
        return 0;
    }

    /**
     * Match and return seconds value
     *
     * @return float|int
     */
    protected function seconds()
    {
        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            // Get fractional seconds
            $seconds = $this->match(Lexer::T_INTEGER) / 3600;

            // Match seconds symbol
            $this->match(Lexer::T_QUOTE);

            // Return value
            return $seconds;
        }

        // No seconds were present so return 0
        return 0;
    }

    /**
     * Match integer or float token and return value
     *
     * @return int|float
     */
    protected function number()
    {
        return $this->match(($this->lexer->isNextToken(Lexer::T_FLOAT) ? Lexer::T_FLOAT : Lexer::T_INTEGER));
    }

    /**
     * Match cardinal direction and return sign
     *
     * @return int
     */
    protected function cardinal()
    {
        // If cardinal direction was not on previous coordinate it can be anything
        if (null === $this->cardinal) {
            $cardinal = $this->match($this->lexer->lookahead['type']);
        } else {
            // Cardinal direction must match requirement
            $cardinal = $this->match($this->cardinal);
        }

        // By default don't change sign
        $sign = 1;

        switch (strtolower($cardinal)) {
            case 's':
                // Southern latitudes are negative
                $sign = -1;
                // no break
            case 'n':
                // Set requirement for second coordinate
                $this->cardinal = Lexer::T_CARDINAL_LONG;
                break;
            case 'w':
                // Western longitudes are negative
                $sign = -1;
                // no break
            case 'e':
                // Set requirement for second coordinate
                $this->cardinal = Lexer::T_CARDINAL_LAT;
                break;
        }

        // Return sign
        return $sign;
    }

    /**
     * Match token and return value
     *
     * @param int $token
     *
     * @return mixed
     */
    protected function match($token)
    {
        if ( ! $this->lexer->isNextToken($token)) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        $this->lexer->moveNext();

        return $this->lexer->token['value'];
    }

    /**
     * Throw descriptive exception for syntax error
     *
     * @param string $expected
     * @param array  $token
     *
     * @throws \UnexpectedValueException
     */
    protected function syntaxError($expected = '', $token = null)
    {
        if (null === $token) {
            $token = $this->lexer->lookahead;
        }

        $message = sprintf(
            '[Syntax Error] line 0, col %d: Error: %s%s in value "%s"',
            isset($token['position']) ? $token['position'] : '-1',
            '' !== $expected ? 'Expected ' . $expected . ', got ' : 'Unexpected ',
            null === $this->lexer->lookahead ? 'end of string.' : '"' . $token['value'] . '"',
            $this->input
        );

        throw new \UnexpectedValueException($message);
    }
}
