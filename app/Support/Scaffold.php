<?php

namespace App\Support;

use JeffersonGoncalves\LaravelZero\PackageScaffold\Namespaces;
use JeffersonGoncalves\LaravelZero\PackageScaffold\Scaffold as SharedScaffold;

/**
 * The generic templates live in jeffersongoncalves/laravel-zero-package-scaffold,
 * shared with filament-plugin-cli. Only this CLI's own namespace derivation
 * stays here, so call sites keep using Scaffold::license() unchanged.
 */
class Scaffold extends SharedScaffold
{
    /**
     * laravel-cep => Vendor\Cep. The `laravel-` prefix is dropped to mirror
     * spatie/laravel-package-tools' shortName(), which is what drives the
     * published config filename. Casing per slug comes from the shared
     * ~/.package/vendornamespace.json; --namespace overrides the lot.
     */
    public static function rootNamespace(string $vendor, string $package): string
    {
        $name = Namespaces::segment((string) preg_replace('/^laravel-/', '', $package));

        return Namespaces::segment($vendor).'\\'.($name ?: 'Package');
    }
}
