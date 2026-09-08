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

it('strips the laravel- prefix from the namespace and class names', function () {
    $dir = sys_get_temp_dir().'/laravel-package-cli-test-'.uniqid();

    $this->artisan('create', [
        'vendor-package' => 'jeffersongoncalves/laravel-cep',
        '--path' => $dir,
        '--no-git' => true,
    ])->assertExitCode(0);

    $composer = json_decode(file_get_contents($dir.'/composer.json'), true);

    expect($composer['name'])->toBe('jeffersongoncalves/laravel-cep')
        ->and($composer['autoload']['psr-4'])->toHaveKey('Jeffersongoncalves\\Cep\\')
        ->and($composer['extra']['laravel']['providers'])->toBe(['Jeffersongoncalves\\Cep\\CepServiceProvider'])
        ->and(is_file($dir.'/src/CepServiceProvider.php'))->toBeTrue()
        ->and(is_file($dir.'/src/Facades/Cep.php'))->toBeTrue()
        ->and(is_file($dir.'/config/cep.php'))->toBeTrue()
        ->and(file_get_contents($dir.'/src/CepServiceProvider.php'))->toContain("->name('laravel-cep')");

    File::deleteDirectory($dir);
});

it('rejects a vendor-package without a slash', function () {
    $this->artisan('create', [
        'vendor-package' => 'not-a-vendor-package',
        '--dry-run' => true,
    ])->assertExitCode(1);
});
