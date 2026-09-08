<?php

use Illuminate\Support\Facades\File;

it('scaffolds a package in dry-run mode without touching disk', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'acme/example-pkg',
        'description' => 'Example package',
        '--path' => $dir,
        '--dry-run' => true,
    ])->assertExitCode(0);

    expect(is_dir($dir))->toBeFalse();
});

it('maps the vendor slug to its real camel-case namespace root', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'jeffersonsimaogoncalves/laravel-cake-settings',
        '--path' => $dir,
        '--no-git' => true,
    ])->assertExitCode(0);

    $composer = json_decode(file_get_contents($dir.'/composer.json'), true);

    expect($composer['autoload']['psr-4'])->toHaveKey('JeffersonSimaoGoncalves\\CakeSettings\\');

    File::deleteDirectory($dir);
});

it('falls back to studly for a vendor outside the map', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'acme/laravel-widget',
        '--path' => $dir,
        '--no-git' => true,
    ])->assertExitCode(0);

    $composer = json_decode(file_get_contents($dir.'/composer.json'), true);

    expect($composer['autoload']['psr-4'])->toHaveKey('Acme\\Widget\\');

    File::deleteDirectory($dir);
});

it('strips the laravel- prefix from the namespace and class names', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'jeffersongoncalves/laravel-cep',
        '--path' => $dir,
        '--no-git' => true,
    ])->assertExitCode(0);

    $composer = json_decode(file_get_contents($dir.'/composer.json'), true);

    expect($composer['name'])->toBe('jeffersongoncalves/laravel-cep')
        ->and($composer['autoload']['psr-4'])->toHaveKey('JeffersonGoncalves\\Cep\\')
        ->and($composer['extra']['laravel']['providers'])->toBe(['JeffersonGoncalves\\Cep\\CepServiceProvider'])
        ->and(is_file($dir.'/src/CepServiceProvider.php'))->toBeTrue()
        ->and(is_file($dir.'/src/Facades/Cep.php'))->toBeTrue()
        ->and(is_file($dir.'/config/cep.php'))->toBeTrue()
        ->and(file_get_contents($dir.'/src/CepServiceProvider.php'))->toContain("->name('laravel-cep')");

    File::deleteDirectory($dir);
});

it('honours --namespace, --keywords and --require', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'jeffersongoncalves/laravel-posthog',
        '--path' => $dir,
        '--namespace' => 'JeffersonGoncalves\\PostHog',
        '--keywords' => 'laravel, posthog, analytics',
        '--require' => 'illuminate/http:^12.0|^13.0,illuminate/support:^12.0|^13.0',
        '--no-git' => true,
    ])->assertExitCode(0);

    $composer = json_decode(file_get_contents($dir.'/composer.json'), true);

    expect($composer['autoload']['psr-4'])->toHaveKey('JeffersonGoncalves\\PostHog\\')
        ->and($composer['extra']['laravel']['providers'])->toBe(['JeffersonGoncalves\\PostHog\\PostHogServiceProvider'])
        ->and($composer['extra']['laravel']['aliases'])->toBe(['PostHog' => 'JeffersonGoncalves\\PostHog\\Facades\\PostHog'])
        ->and($composer['keywords'])->toBe(['laravel', 'posthog', 'analytics'])
        ->and($composer['type'])->toBe('library')
        ->and($composer['require'])->not->toHaveKey('illuminate/contracts')
        ->and($composer['require'])->toHaveKey('illuminate/support')
        ->and(is_file($dir.'/src/PostHogServiceProvider.php'))->toBeTrue()
        ->and(is_file($dir.'/src/Facades/PostHog.php'))->toBeTrue()
        ->and(is_file($dir.'/config/posthog.php'))->toBeTrue();

    File::deleteDirectory($dir);
});

it('rejects a vendor-package without a slash', function () {
    $this->artisan('create', [
        'vendor-package' => 'not-a-vendor-package',
        '--dry-run' => true,
    ])->assertExitCode(1);
});
