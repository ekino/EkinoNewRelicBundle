# Changelog

## v2.0.0-beta3

### Changed

- Moved "instrument" to the root level
- The `AdaptiveInteractor` is now the default interactor. 

### Fixed

- Bug where logging deprecations did not work. 

## v2.0.0-beta2

### Changed

- Add default "deployment_names"
- Updated interface variable names to match the NewRelic extension.


## v2.0.0-beta1

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
