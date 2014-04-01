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
    private $cardinal;

    /**
     * @var int
     */
    private $ascii;

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
     * @return float|int|array
     * @throws \UnexpectedValueException
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

        // There should be no additional tokens
        if (null !== $this->lexer->lookahead) {
            throw $this->syntaxError('end of string');
        }

        // Return coordinate array
        return array($x, $y);
    }

    /**
     * @return float|int
     */
    protected function coordinate()
    {
        // By default don't change sign
        $sign = 1;

        // Match minus if cardinal direction has not been seen
        if ( ! ($this->cardinal > 0) && $this->lexer->isNextToken(Lexer::T_MINUS)) {
            $this->match(Lexer::T_MINUS);

            // Matching minus changes sign
            $sign = -1;
        }

        // Get coordinate value
        $coordinate = $this->degrees();

        // Get sign from cardinal direction if requirement defined by first coordinate and minus not matched
        if ($sign > 0 && $this->cardinal > 0) {
            return $this->cardinal($coordinate);
        }

        // Get sign from cardinal direction if it's present, this is first coordinate, and minus not matched
        if ($sign > 0 && null === $this->cardinal && $this->lexer->isNextTokenAny(array(Lexer::T_CARDINAL_LAT, Lexer::T_CARDINAL_LON))) {
            return $this->cardinal($coordinate);
        }

        // Remember there was no cardinal direction on first coordinate
        $this->cardinal = -1;

        // Return value with sign
        return $sign * $coordinate;
    }

    /**
     * Match and return degrees value
     *
     * @return float|int
     */
    protected function degrees()
    {
        // If degrees is a float there will be no minutes or seconds
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            // Get degree value
            $degrees = $this->match(Lexer::T_FLOAT);

            // Degree float values may be followed by degree symbol
            $this->ascii();

            // Return value
            return $degrees;
        }

        // If degrees isn't a float it must be an integer
        $degrees = $this->number();

        // If integer is not followed by a degree symbol this value is complete
        if ( ! $this->ascii()) {
            return $degrees;
        }

        // Grab peek of next token since we can't array dereference result in PHP 5.3
        $glimpse = $this->lexer->glimpse();

        // If next token is a number followed by degree symbol, when tuple separator is space instead of comma, this value is complete
        if ($this->lexer->isNextTokenAny(array(Lexer::T_INTEGER, Lexer::T_FLOAT)) && Lexer::T_DEGREE === $glimpse['type']) {
            return $degrees;
        }

        // Add minutes to value
        $degrees += $this->minutes();

        // Return value
        return $degrees;
    }

    /**
     * Match ascii degree symbol, returns true if present
     *
     * @return bool
     */
    protected function ascii()
    {
        // Match degree symbol if requirement set
        if (true === $this->ascii) {
            return (bool) $this->match(Lexer::T_DEGREE);
        }

        // If requirement not set match degree if present
        if (null === $this->ascii && $this->lexer->isNextToken(Lexer::T_DEGREE)) {
            // Set requirement for any remaining value
            return $this->ascii = (bool) $this->match(Lexer::T_DEGREE);
        }

        // Set requirement for any remaining value
        return $this->ascii = false;
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
            $minutes = $this->match(Lexer::T_FLOAT) / 60;

            // Match minutes symbol
            $this->match(Lexer::T_APOSTROPHE);

            // return value
            return $minutes;
        }

        // If minutes is an integer parse value
        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            // Get fractional minutes
            $minutes = $this->match(Lexer::T_INTEGER) / 60;

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
        // Seconds value can be an integer or float
        if ($this->lexer->isNextTokenAny(array(Lexer::T_INTEGER, Lexer::T_FLOAT))) {
            // Get fractional seconds
            $seconds = $this->number() / 3600;

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
     * @throws \UnexpectedValueException
     */
    protected function number()
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
     * @throws \Exception
     */
    protected function cardinal($value)
    {
        // If cardinal direction was not on previous coordinate it can be anything
        if (null === $this->cardinal) {
            $cardinal = $this->match($this->lexer->lookahead['type']);
        } else {
            // Cardinal direction must match requirement
            $cardinal = $this->match($this->cardinal);
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
                $this->cardinal = Lexer::T_CARDINAL_LON;
                // Latitude values are +/- 90
                $range = 90;
                break;
            case 'w':
                // Western longitudes are negative
                $sign = -1;
                // no break
            case 'e':
                // Set requirement for second coordinate
                $this->cardinal = Lexer::T_CARDINAL_LAT;
                // Longitude values are +/- 180
                $range = 180;
                break;
        }

        // Verify unsigned value is in range
        if ($value > $range) {
            throw new \Exception(); // TODO
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
     * @throws \UnexpectedValueException
     */
    protected function match($token)
    {
        // If next token isn't type specified throw error
        if ( ! $this->lexer->isNextToken($token)) {
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
     * @return \UnexpectedValueException
     */
    protected function syntaxError($expected = null, $token = null)
    {
        if (null === $token) {
            $token = $this->lexer->lookahead;
        }

        if (null === $expected) {
            $expected = 'Unexpected ';
        } else {
            $expected = sprintf('Expected %s, got ', $expected);
        }

        if (null === $this->lexer->lookahead) {
            $found = 'end of string.';
        } else {
            $found = sprintf('"%s"', $token['value']);
        }

        $message = sprintf(
            '[Syntax Error] line 0, col %d: Error: %s%s in value "%s"',
            isset($token['position']) ? $token['position'] : '-1',
            $expected,
            $found,
            $this->input
        );

        return new \UnexpectedValueException($message);
    }
}
