# geo-parser

This package contains a lexer and parser for geographic coordinate strings. I created it for my doctrine2-spatial
package but can be used on its own.

I whipped this up in a matter of hours so there is currently no documentation. To see it in use, take a look at the
tests in tests/CrEOF/Geo/Tests.

## Supported Formats

Both single values and tuples are supported. Samples of formats supported (spaces are ignored):

* 40
* -40
* 40°
* -40°
* 40° N
* 40° S
* 45.24
* 45.24°
* 45.24° S
* 40° 26' 46" N
* 40° N 79° W
* 40 79
* 40° 79°
* 40, 79
* 40°, 79°
* 40° 26' 46" N 79° 58' 56" W
* 40.4738° N, 79.553° W
* 40.4738° S, 79.553° W
* 40° 26.222' N 79° 58.52' E
* 40°26.222'N 79°58.52'E
* 40°26.222' 79°58.52'
* 40.222° -79.5852°
* 40.222°, -79.5852°

## Output

The parser will return a integer/float or an array containing a pair of these values.

## Todo
* Match pairs contained in parenthesis?
* Allow colon as a separator? (like 108:53:94W)
