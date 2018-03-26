# Changelog

## v2.0.0

### Added

- All functions provided by the NewRelic PHP extension are now supported in the `NewRelicInteractorInterface`.
- Added a new `deprecations` parameter to logs `E_USER_DEPRECATED`.
- Added a new `monolog` parameter to send logs to new relic.

### Changed

- Command Configuration explicit
- The configuration syntax
- The bundle uses class-named service ids. See `UPGRADE-2.0.md` for the exhaustive list of changes

### Removed

- Support for Silex
- Support for PHP < 7.1
- Support for Symfony < 3.4
