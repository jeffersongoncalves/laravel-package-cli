# Changelog

All notable changes to this project will be documented in this file.

## [1.0.7] - 2026-09-08

### Features

- Add --namespace, --keywords and --require to the create command

## [1.0.6] - 2026-09-08

### Bug Fixes

- Strip laravel- prefix when deriving the package namespace

## [1.0.5] - 2026-09-07

### Bug Fixes

- Move runtime deps out of require-dev
- Correct generated composer.json for new Laravel packages

### Documentation

- Add usage examples and descriptive parameter tables to README

### Other

- Revert "fix: move runtime deps out of require-dev"

This reverts commit fdfb324622135eedda2831ac8d3b15a3fa4bd832.

## [1.0.4] - 2026-09-07

### Bug Fixes

- Keep require-dev autoload in the compiled PHAR

## [1.0.3] - 2026-09-07

### Bug Fixes

- Point composer bin to prebuilt PHAR instead of source stub

## [1.0.2] - 2026-09-07

### Features

- Add self-update command

## [1.0.1] - 2026-09-07

### CI/CD

- Add release workflow to build and publish PHAR

## [1.0.0] - 2026-09-07

### Bug Fixes

- Use real git identity for composer.json author
- Raise php floor to ^8.3, test PHP 8.4 only in CI

### Documentation

- Add portfolio banner

### Features

- Scaffold laravel-package-cli


