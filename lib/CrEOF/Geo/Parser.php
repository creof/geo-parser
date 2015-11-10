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

namespace CrEOF\Geo;

use CrEOF\Geo\Exception\RangeException;
use CrEOF\Geo\Exception\UnexpectedValueException;

/**
 * Parser for geographic coordinate strings
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class Parser
{
    /**
     * Original input string
     *
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
    private $nextCardinal;

    /**
     * @var int
     */
    private $nextSymbol;

    /**
     * Constructor
     *
     * Setup up instance properties
     *
     * @param string $input
     */
    public function __construct($input)
    {
        // Save input string for use in messages
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
     * Match and return single value or pair
     *
     * @return float|int|array
     * @throws UnexpectedValueException
     */
    private function point()
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

        // There should be no additional tokens
        if (null !== $this->lexer->lookahead) {
            throw $this->syntaxError('end of string');
        }

        // Return coordinate array
        return array($x, $y);
    }

    /**
     * Match and return single value
     *
     * @return float|int
     */
    private function coordinate()
    {
        // By default don't change sign
        $sign = false;

        // Match minus if cardinal direction has not been seen
        if (! ($this->nextCardinal > 0) && $this->lexer->isNextTokenAny(array(Lexer::T_PLUS, Lexer::T_MINUS))) {
            if ($this->lexer->isNextToken(Lexer::T_PLUS)) {
                // Match plus and set sign
                $this->match(Lexer::T_PLUS);

                $sign = 1;
            } else {
                // Match minus and set sign
                $this->match(Lexer::T_MINUS);

                $sign = -1;
            }
        }

        // Get coordinate value
        $coordinate = $this->degrees();

        // If minus not matched get sign from cardinal direction if requirement defined or if it's present and the first coordinate
        if (false === $sign && ($this->nextCardinal > 0 || (null === $this->nextCardinal && $this->lexer->isNextTokenAny(array(Lexer::T_CARDINAL_LAT, Lexer::T_CARDINAL_LON))))) {
            return $this->cardinal($coordinate);
        }

        // Remember there was no cardinal direction on first coordinate
        $this->nextCardinal = -1;

        // Return value with sign if set
        return (false === $sign ? 1 : $sign) * $coordinate;
    }

    /**
     * Match and return degrees value
     *
     * @return float|int
     */
    private function degrees()
    {
        // Reset symbol requirement
        if ($this->nextSymbol === Lexer::T_APOSTROPHE || $this->nextSymbol === Lexer::T_QUOTE) {
            $this->nextSymbol = Lexer::T_DEGREE;
        }

        // If degrees is a float there will be no minutes or seconds
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            // Get degree value
            $degrees = $this->match(Lexer::T_FLOAT);

            // Degree float values may be followed by degree symbol
            if ($this->lexer->isNextToken(Lexer::T_DEGREE)) {
                $this->match(Lexer::T_DEGREE);

                // Set requirement for any remaining value
                $this->nextSymbol = Lexer::T_DEGREE;
            }

            // Return value
            return $degrees;
        }

        // If degrees isn't a float it must be an integer
        $degrees = $this->number();

        // If integer is not followed by a symbol this value is complete
        if (! $this->symbol()) {
            return $degrees;
        }

        // Grab peek of next token since we can't array dereference result in PHP 5.3
        $glimpse = $this->lexer->glimpse();

        // If a colon hasn't been matched, and next token is a number followed by degree symbol, when tuple separator is space instead of comma, this value is complete
        if (Lexer::T_COLON !== $this->nextSymbol && $this->lexer->isNextTokenAny(array(Lexer::T_INTEGER, Lexer::T_FLOAT)) && Lexer::T_DEGREE === $glimpse['type']) {
            return $degrees;
        }

        // Add minutes to value
        $degrees += $this->minutes();

        // Return value
        return $degrees;
    }

    /**
     * Match value component symbol if required or present
     *
     * @return bool|int
     */
    private function symbol()
    {
        // Match symbol if requirement set and update requirement for next symbol
        switch ($this->nextSymbol) {
            case Lexer::T_COLON:
                $this->match(Lexer::T_COLON);

                return $this->nextSymbol;
            case Lexer::T_DEGREE:
                $this->match(Lexer::T_DEGREE);

                // Next symbol will be minutes
                return $this->nextSymbol = Lexer::T_APOSTROPHE;
            case Lexer::T_APOSTROPHE:
                $this->match(Lexer::T_APOSTROPHE);

                // Next symbol will be seconds
                return $this->nextSymbol = Lexer::T_QUOTE;
            case Lexer::T_QUOTE:
                $this->match(Lexer::T_QUOTE);

                return $this->nextSymbol;
        }

        // If requirement not set match symbol if present
        if (null === $this->nextSymbol && $this->lexer->isNextToken(Lexer::T_COLON)) {
            $this->match(Lexer::T_COLON);

            // Set requirement for any remaining value
            return $this->nextSymbol = Lexer::T_COLON;
        }

        if (null === $this->nextSymbol && $this->lexer->isNextToken(Lexer::T_DEGREE)) {
            $this->match(Lexer::T_DEGREE);

            // Set requirement for any remaining value
            return $this->nextSymbol = Lexer::T_APOSTROPHE;
        }

        // Set requirement for any remaining value
        return $this->nextSymbol = false;
    }

    /**
     * Match and return minutes value
     *
     * @return float|int
     * @throws RangeException
     */
    private function minutes()
    {
        // If using colon or minutes is an integer parse value
        if (Lexer::T_COLON === $this->nextSymbol || $this->lexer->isNextToken(Lexer::T_INTEGER)) {
            $minutes = $this->match(Lexer::T_INTEGER);

            // Throw exception if minutes are greater than 60
            if ($minutes > 60) {
                throw $this->rangeError('Minutes', 60);
            }

            // Get fractional minutes
            $minutes = $minutes / 60;

            // If using colon and one doesn't follow value is done
            if (Lexer::T_COLON === $this->nextSymbol && ! $this->lexer->isNextToken(Lexer::T_COLON)) {
                return $minutes;
            }

            // Match minutes symbol
            $this->symbol();

            // Add seconds to value
            $minutes += $this->seconds();

            // Return value
            return $minutes;
        }

        // If minutes is a float there will be no seconds
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            $minutes = $this->match(Lexer::T_FLOAT);

            // Throw exception if minutes are greater than 60
            if ($minutes > 60) {
                throw $this->rangeError('Minutes', 60);
            }

            // Get fractional minutes
            $minutes = $minutes / 60;

            // Match minutes symbol
            $this->symbol();

            // return value
            return $minutes;
        }

        // No minutes were present so return 0
        return 0;
    }

    /**
     * Match and return seconds value
     *
     * @return float|int
     * @throws RangeException
     */
    private function seconds()
    {
        // Seconds value can be an integer or float
        if ($this->lexer->isNextTokenAny(array(Lexer::T_INTEGER, Lexer::T_FLOAT))) {
            $seconds = $this->number();

            // Throw exception if seconds are greater than 60
            if ($seconds > 60) {
                throw $this->rangeError('Seconds', 60);
            }

            // Get fractional seconds
            $seconds = $seconds / 3600;

            // Match seconds symbol if requirement not colon
            if (Lexer::T_COLON !== $this->nextSymbol) {
                $this->symbol();
            }

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
     * @throws UnexpectedValueException
     */
    private function number()
    {
        // If next token is a float match and return it
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            return $this->match(Lexer::T_FLOAT);
        }

        // If next token is an integer match and return it
        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            return $this->match(Lexer::T_INTEGER);
        }

        // Throw exception since no match
        throw $this->syntaxError('CrEOF\Geo\Lexer::T_INTEGER or CrEOF\Geo\Lexer::T_FLOAT');
    }

    /**
     * Match cardinal direction and return sign
     *
     * @param int|float $value
     *
     * @return int
     * @throws RangeException
     */
    private function cardinal($value)
    {
        // If cardinal direction was not on previous coordinate it can be anything
        if (null === $this->nextCardinal) {
            $cardinal = $this->match(Lexer::T_CARDINAL_LON === $this->lexer->lookahead['type'] ? Lexer::T_CARDINAL_LON : Lexer::T_CARDINAL_LAT);
        } else {
            // Cardinal direction must match requirement
            $cardinal = $this->match($this->nextCardinal);
        }

        // By default don't change sign
        $sign  = 1;
        // Define value range
        $range = 0;

        switch (strtolower($cardinal)) {
            case 's':
                // Southern latitudes are negative
                $sign = -1;
                // no break
            case 'n':
                // Set requirement for second coordinate
                $this->nextCardinal = Lexer::T_CARDINAL_LON;
                // Latitude values are +/- 90
                $range = 90;
                break;
            case 'w':
                // Western longitudes are negative
                $sign = -1;
                // no break
            case 'e':
                // Set requirement for second coordinate
                $this->nextCardinal = Lexer::T_CARDINAL_LAT;
                // Longitude values are +/- 180
                $range = 180;
                break;
        }

        // Throw exception if value is out of range
        if ($value > $range) {
            throw $this->rangeError('Degrees', $range, (-1 * $range));
        }

        // Return value with sign
        return $value * $sign;
    }

    /**
     * Match token and return value
     *
     * @param int $token
     *
     * @return mixed
     * @throws UnexpectedValueException
     */
    private function match($token)
    {
        // If next token isn't type specified throw error
        if (! $this->lexer->isNextToken($token)) {
            throw $this->syntaxError($this->lexer->getLiteral($token));
        }

        // Move lexer to next token
        $this->lexer->moveNext();

        // Return the token value
        return $this->lexer->token['value'];
    }

    /**
     * Create exception with descriptive error message
     *
     * @param string $expected
     * @param array  $token
     *
     * @return UnexpectedValueException
     */
    private function syntaxError($expected = null, $token = null)
    {
        if (null === $expected) {
            $expected = 'Unexpected';
        } else {
            $expected = sprintf('Expected %s, got', $expected);
        }

        if (null === $token) {
            $token = $this->lexer->lookahead;
        }

        if (null === $this->lexer->lookahead) {
            $found = 'end of string.';
        } else {
            $found = sprintf('"%s"', $token['value']);
        }

        $message = sprintf(
            '[Syntax Error] line 0, col %d: Error: %s %s in value "%s"',
            isset($token['position']) ? $token['position'] : '-1',
            $expected,
            $found,
            $this->input
        );

        return new UnexpectedValueException($message);
    }

    /**
     * Create out of range exception
     *
     * @param string $type
     * @param int    $high
     * @param int    $low
     *
     * @return RangeException
     */
    private function rangeError($type = null, $high = null, $low = null)
    {
        if (null === $type) {
            $type = 'Value';
        }

        $range = null;

        switch (true) {
            case (null === $high && null === $low):
                $range = 'out of range';
                break;
            case (null !== $high && null === $low):
                $range = sprintf('greater than %d', $high);
                break;
            case (null !==$high && null !== $low):
                $range = sprintf('out of range %d to %d', $low, $high);
                break;
        }

        $message = sprintf('[Range Error] Error: %s %s in value "%s"', $type, $range, $this->input);

        return new RangeException($message);
    }
}
