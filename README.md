<div class="filament-hidden">

<!-- banner: art/jeffersongoncalves-laravel-package-cli.png (generate via portfolio-banner skill) -->

</div>

# Laravel Package CLI

Scaffold new open-source Laravel packages with git already configured, built with [Laravel Zero](https://laravel-zero.com/). Fully non-interactive — every input is an argument or a flag, so it's meant to be driven by an AI agent (e.g. Claude Code's `laravel-package-creator` skill) as much as by a human.

<p align="center">
  <a href="https://github.com/jeffersongoncalves/laravel-package-cli/actions"><img src="https://github.com/jeffersongoncalves/laravel-package-cli/actions/workflows/run-tests.yml/badge.svg" alt="Tests" /></a>
  <a href="https://packagist.org/packages/jeffersongoncalves/laravel-package-cli"><img src="https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-package-cli" alt="Total Downloads" /></a>
  <a href="https://github.com/jeffersongoncalves/laravel-package-cli/blob/main/LICENSE"><img src="https://img.shields.io/github/license/jeffersongoncalves/laravel-package-cli" alt="License" /></a>
  <img src="https://img.shields.io/badge/php-%3E%3D8.2-8892BF" alt="PHP 8.2+" />
</p>

## What it does

`laravel-package create vendor/package "Description"` generates the mechanical, repeatable part of a new Spatie-style Laravel package:

- Directory skeleton (`src/`, `config/`, `database/migrations/`, `resources/lang/{en,pt_BR}/`, `tests/`, `art/`, `.github/workflows/`)
- `composer.json` with `spatie/laravel-package-tools`, Pest, Larastan, Pint wired up
- Service Provider (`PackageServiceProvider`) and Facade stubs
- `phpstan.neon.dist`, `phpunit.xml.dist`, `.editorconfig`, `.gitattributes`, `.gitignore`, `LICENSE.md`, `CHANGELOG.md`, `README.md`
- CI workflows: `tests.yml`, `pint.yml`, `phpstan.yml`, `update-changelog.yml`
- `git init`, first commit on `main`

It deliberately does **not** write the package's actual logic (Service Provider bindings, config values, README body, tests, banner) — that's judgment work left to whoever (human or agent) is building the package on top of this scaffold.

## Requirements

- PHP 8.2+
- Git

## Installation

```bash
composer global require jeffersongoncalves/laravel-package-cli
```

Or clone and build locally:

```bash
git clone https://github.com/jeffersongoncalves/laravel-package-cli.git
cd laravel-package-cli
composer install
php laravel-package app:build laravel-package
```

## Usage

```bash
laravel-package create jeffersongoncalves/laravel-cep "Brazilian CEP (postal code) lookup for Laravel"
```

### Options

| Option | Description |
|--------|-------------|
| `--path=DIR` | Target directory (default: `./<package>` under the current directory) |
| `--author="Name"` | Defaults to `git config user.name` |
| `--email=EMAIL` | Defaults to `git config user.email` |
| `--no-git` | Skip `git init`/commit |
| `--dry-run` | Print the planned file list and git commands, write nothing |

Every argument/option is designed for scripted, non-interactive invocation — no prompts are ever shown.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please see [SECURITY](.github/SECURITY.md).

## Credits

- [Jefferson Goncalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
