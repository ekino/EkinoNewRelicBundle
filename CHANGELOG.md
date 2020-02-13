# Changelog

## v2.2.0

### Added

- Added `api_host` configuration property used by `NotifyDeploymentCommand`

## v2.1.3

### Added

- Test again PHP 7.4
- Use typehinted alias in EventListener

### Fixed

- Wrong event handled in RequestListener

## v2.1.2

### Fixed

- Fixed compatibility issues with Symfony 5.0
- Handle new ResponseEvent, RequestEvent and ExceptionEvent in EventListeners

## v2.1.1

### Added

- Allow Symfony 5.0

## v2.1.0

### Added

- More detail/context when PSR-3 Logging the Newrelic transactions

### Fixed

- Even when handling a streamed response should call 'endTransaction' on onKernelResponse even
- Warnings in PHP 7.4
- Stop using Twig deprecated classes

## v2.0.2

### Changed

- Remove deprecations triggered by Symfony 4.0.
- Excluded tests from classmap.

### Fixed

- Fixed call to non-allowed method `setContent` on a `StreamedResponse`.
- Fixed multiple decoration of error handler when the bundle is often started and stopped like in test suite.
- Fixed issue in monolog's service configuration that does not allows application's services or aliases.

## v2.0.1

### Fixed

- Fixed type error when configuration's property `deployment_names` is not a string

## v2.0.0

### Changed

- Update the return type annotation of `NewRelicInteractorInterface::disableAutoRUM` to `?bool`
  to match the latest changes in the NewRelic API.

## v2.0.0-beta5

### Fixed

- Memory leak in the `ResponseListener` that may cause issues on large HTML responses.
- Fixed type error when no Content-Type header was returned.
- Make sure `NewRelicInteractor::disableAutoRUM` always returns true.

## v2.0.0-beta4

### Changed

- Changed the configuration for monolog's channel to a configuration similar to MonologBundle.

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
