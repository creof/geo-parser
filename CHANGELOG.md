# Change Log
All notable changes to this project will be documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased][unreleased]
### Added
- Change log file to chronicle changes.
- Dependency on SPL extension to composer.json since the package exceptions extend them.
- Stub TODO.md file.
- CONTRIBUTING.md file with guidelines.
- Travis CI config
- Code Climate config
- Add support for unicode prime and double prime.
- Tests for uncovered parser branches.
- Change property names in parser to more accurately indicate what they're for.
### Changed
- Use string compare instead of regex for cardinal direction.
- Remove unneeded deps for phpmd and phpcs - Code Climate with handle this.
- Match seconds symbol with symbol().

