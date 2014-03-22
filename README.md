# geo-parser

This package contains a lexer and parser for geographic coordinate strings. I created it for my doctrine2-spatial
package but can be used on its own.

I whipped this up in a matter of hours so there is currently no documentation. To see it in use, take a look at the
tests in tests/CrEOF/Geo/Tests.

## Supported Formats

Some samples of formats supported (spaces don't matter):

* 40° 26' 46" N 79° 58' 56" W
* 40° 26' 46" N, 79° 58' 56" W
* 40°26.222'N 79°58.52'E
* -40°26.222', -79°58.52'
* 40.4738° N, 79.553° W
* 40.4738°, 79.553°
* 40.4738° -79.553°
