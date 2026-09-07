<?php

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

it('rejects a vendor-package without a slash', function () {
    $this->artisan('create', [
        'vendor-package' => 'not-a-vendor-package',
        '--dry-run' => true,
    ])->assertExitCode(1);
});
