# Change Log
All notable changes to this project will be documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Support for numbers in scientific notation.
### Changed
- Parser constructor no longer requires a value enabling instance reuse.
- Lexer constructor no longer requires a value enabling instance reuse.
- Lexer instance used in Parser now a static var.
- Tests now use Composer autoload.
- PHPUnit XML now conformant.
- Documentation updated with new usage pattern.
### Removed
- TestInit no longer needed

## [2.0.0] - 2015-11-18
### Added
- Change base namespace to CrEOF\Geo\String to avoid class collision with other CrEOF packages.

## [1.0.1] - 2015-11-17
### Added
- Exclude fingerprint for Code Climate fixme engine to ignore "Stub TODO.md file." in changelog.
### Changed
- Removed code for unused conditions in Parser error methods.
- Removed case for token T_PERIOD in getType method of Lexer, it's not used in Parser.

## [1.0.0] - 2015-11-11
### Added
- Change log file to chronicle changes.
- Dependency on SPL extension to composer.json since the package exceptions extend them.
- Stub TODO.md file.
- CONTRIBUTING.md file with guidelines.
- Travis CI config
- Code Climate config
- Add support for unicode prime and double prime.
- Tests for uncovered parser branches.
### Changed
- Use string compare instead of regex for cardinal direction.
- Remove unneeded deps for phpmd and phpcs - Code Climate with handle this.
- Match seconds symbol with symbol().
- Change property names in parser to more accurately indicate what they're for.

