# creof/geo-parser

Lexer and parser library for geometric and geographic string values.

This library is used in the creof/geo library.

Sample code can be found in the unit tests.

## Supported Formats

Both single values and pairs are supported. Some samples of supported formats are below, though not every possible iteration may be explicitly specified:

1. Simple single signed values
 * 40
 * -40
 * -8.543
 * +132
 * +77.2

2. Simple single signed values with degree symbol
 * 40°
 * -40°
 * -5.234°
 * +43°
 * +38.43°

3. Single unsigned values with or without degree symbol, and cardinal direction
 * 40° N
 * 40 S
 * 56.242 E

4. Single values of signed integer degrees with degree symbol, and decimal minutes with apostrophe
 * 40° 26.222'
 * -65° 32.22'
 * +165° 52.22'

5. Single values of unsigned integer degrees with degree symbol, decimal minutes with apostrophe, and cardinal direction
 * 40° 26.222' E
 * 65° 32.22' S

6. Single values of signed integer degrees with degree symbol, integer minutes with apostrophe, and optional integer or decimal seconds with quote
 * 40° 26' 46"
 * -79° 58' 56"
 * 93° 19' 25.8"
 * +120° 19' 25.8"

6. Single values of signed integer degrees with colon symbol, integer minutes, and optional colon and integer or decimal seconds
 * +40:26:46
 * -79:58:56
 * 93:19:25.8

7. Single values of unsigned integer degrees with degree symbol, integer minutes with apostrophe, optional integer or decimal seconds with quote, and cardinal direction
 * 40° 26' 46" S
 * 99° 58' 56" W
 * 44° 58' 53.9" N

7. Single values of unsigned integer degrees with colon symbol, integer minutes with, optional colon and integer or decimal seconds, and cardinal direction
 * 40:26:46 S
 * 99:58:56 W
 * 44:58:53.9 N

8. Two of any one format separated by space(s)

9. Two of any one format separated by a comma

## Return

The parser will return a integer/float or an array containing a pair of these values.

## Todo
* Match pairs contained in parenthesis?
* Allow colon as a separator? (like 108:53:94W)
