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
     * @param string $input
     */
    public function __construct($input)
    {
        $this->input = $input;
        $this->lexer = new Lexer($input);
    }

    /**
     * @return array
     */
    public function parse()
    {
        $this->lexer->moveNext();

        return $this->point();
    }

    /**
     * @return array
     */
    protected function point()
    {
        $x = $this->coordinate();

        if (null === $this->lexer->lookahead) {
            return $x;
        }

        if ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
        }

        $y = $this->coordinate();

        return array($x, $y);
    }

    /**
     * @return float|int
     */
    protected function coordinate()
    {
        $coordinate = $this->degrees();

        if ($this->cardinal > 0) {
            return $coordinate * $this->cardinal();
        }

        if (null === $this->cardinal && $this->lexer->isNextTokenAny(array(Lexer::T_CARDINAL_LAT, Lexer::T_CARDINAL_LONG))) {
            return $coordinate * $this->cardinal();
        }

        $this->cardinal = -1;

        return $coordinate;
    }

    /**
     * @return float|int
     */
    protected function degrees()
    {
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            $value = $this->number();

            $this->match(Lexer::T_DEGREE);

            return $value;
        }

        $value = $this->number();

        $this->match(Lexer::T_DEGREE);

        $value += $this->minutes();

        return $value;
    }

    /**
     * @return float|int
     */
    protected function minutes()
    {
        if ($this->lexer->isNextToken(Lexer::T_FLOAT)) {
            $value = $this->number() / 60;

            $this->match(Lexer::T_APOSTROPHE);

            return $value;
        }

        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            $value = $this->number() / 60;

            $this->match(Lexer::T_APOSTROPHE);

            $value += $this->seconds();

            return $value;
        }

        return 0;
    }

    /**
     * @return float|int
     */
    protected function seconds()
    {
        if ($this->lexer->isNextToken(Lexer::T_INTEGER)) {
            $value = $this->match(Lexer::T_INTEGER) / 3600;

            $this->match(Lexer::T_QUOTE);

            return $value;
        }

        return 0;
    }

    /**
     * @return int|float
     */
    protected function number()
    {
        return $this->match(($this->lexer->isNextToken(Lexer::T_FLOAT) ? Lexer::T_FLOAT : Lexer::T_INTEGER));
    }

    /**
     * @return int
     */
    protected function cardinal()
    {
        if (null === $this->cardinal) {
            $cardinal = $this->match($this->lexer->lookahead['type']);
        } else {
            $cardinal = $this->match($this->cardinal);
        }

        $value = 1;

        switch (strtolower($cardinal)) {
            case 's':
                $value = -1;
                // no break
            case 'n':
                $this->cardinal = Lexer::T_CARDINAL_LONG;
                break;
            case 'w':
                $value = -1;
                // no break
            case 'e':
                $this->cardinal = Lexer::T_CARDINAL_LAT;
                break;
        }

        return $value;
    }

    /**
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

        $tokenPos = (isset($token['position'])) ? $token['position'] : '-1';

        $message  = 'line 0, col ' . $tokenPos . ': Error: ';
        $message .= ('' !== $expected) ? 'Expected ' . $expected . ', got ' : 'Unexpected ';
        $message .= (null === $this->lexer->lookahead) ? 'end of string.' : '"' . $token['value'] . '"';

        $message = sprintf('[Syntax Error] %s in value "%s"', $message, $this->input);

        throw new \UnexpectedValueException($message);
    }
}
