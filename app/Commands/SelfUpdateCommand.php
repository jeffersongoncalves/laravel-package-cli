<?php

namespace App\Commands;

use JeffersonGoncalves\LaravelZero\SelfUpdate\PharUpdater;
use JeffersonGoncalves\LaravelZero\SelfUpdate\SelfUpdateCommand as BaseSelfUpdateCommand;

class SelfUpdateCommand extends BaseSelfUpdateCommand
{
    protected $description = 'Update the laravel-package CLI to the latest version';

    protected function githubRepo(): string
    {
        return 'jeffersongoncalves/laravel-package-cli';
    }

    protected function assetName(): string
    {
        return 'laravel-package.phar';
    }

    protected function tempPrefix(): string
    {
        return 'laravel_package_';
    }

    protected function currentVersion(): string
    {
        return (string) config('app.version', 'unreleased');
    }

    protected function makeUpdater(): PharUpdater
    {
        return $this->getLaravel()->make(PharUpdater::class);
    }
}
