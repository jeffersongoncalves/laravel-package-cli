<?php

namespace App\Commands;

use App\Support\Scaffold;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

class CreateCommand extends Command
{
    /**
     * Fully non-interactive: every input comes from arguments/options so an
     * AI agent (or any script) can call this without a TTY. Never add ->ask()
     * or ->confirm() here.
     *
     * @var string
     */
    protected $signature = 'create
        {vendor-package : vendor/package, e.g. jeffersongoncalves/laravel-cep}
        {description? : short description of the package}
        {--path= : target directory (default: ./<package> under cwd)}
        {--author= : defaults to `git config user.name`}
        {--email= : defaults to `git config user.email`}
        {--no-git : skip git init/commit}
        {--dry-run : print planned actions, write nothing}';

    protected $description = 'Scaffold a new open-source Laravel package: directory skeleton, boilerplate files, CI workflows, and a git repo on main with the first commit made.';

    public function handle(): int
    {
        $vendorPackage = (string) $this->argument('vendor-package');
        if (! str_contains($vendorPackage, '/')) {
            $this->components->error("Expected vendor/package, got: {$vendorPackage}");

            return self::FAILURE;
        }
        [$vendor, $package] = explode('/', $vendorPackage, 2);
        $description = (string) ($this->argument('description') ?? '');

        $dryRun = (bool) $this->option('dry-run');
        $noGit = (bool) $this->option('no-git');

        $namespace = Scaffold::studly($vendor).'\\'.Scaffold::studly($package);
        $serviceProvider = Scaffold::studly($package).'ServiceProvider';
        $facade = Scaffold::studly($package);
        $title = Scaffold::studly($package);
        $configFile = $package;

        $author = $this->option('author') ?: trim((string) Process::run('git config --get user.name')->output()) ?: 'Jefferson Gonçalves';
        $email = $this->option('email') ?: trim((string) Process::run('git config --get user.email')->output());
        $year = date('Y');

        $dir = $this->option('path') ?: getcwd().DIRECTORY_SEPARATOR.$package;

        $files = [];
        $write = function (string $relative, string $content) use ($dir, $dryRun, &$files): void {
            $path = $dir.'/'.$relative;
            if (File::exists($path)) {
                $files[] = "skip (exists) {$relative}";

                return;
            }
            if ($dryRun) {
                $files[] = "write (dry-run) {$relative}";

                return;
            }
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $content);
            $files[] = "write {$relative}";
        };

        foreach ([
            'src/Commands', 'src/Facades', 'config', 'database/migrations',
            'resources/lang/en', 'resources/lang/pt_BR', 'resources/views',
            'tests/Feature', 'tests/Unit', 'art', '.github/workflows',
        ] as $d) {
            if (! $dryRun) {
                File::ensureDirectoryExists($dir.'/'.$d);
            }
        }

        $composerJson = [
            'name' => "$vendor/$package",
            'description' => $description,
            'keywords' => ['laravel', $package],
            'homepage' => "https://github.com/$vendor/$package",
            'license' => 'MIT',
            'authors' => [['name' => $author, 'email' => $email]],
            'require' => [
                'php' => '^8.2',
                'spatie/laravel-package-tools' => '^1.16',
                'illuminate/contracts' => '^12.0|^13.0',
            ],
            'require-dev' => [
                'larastan/larastan' => '^3.0',
                'laravel/pint' => '^1.21',
                'orchestra/testbench' => '^10.0|^11.0',
                'pestphp/pest' => '^3.0|^4.0',
                'pestphp/pest-plugin-laravel' => '^3.0|^4.0',
            ],
            'autoload' => ['psr-4' => ["$namespace\\" => 'src/']],
            'autoload-dev' => ['psr-4' => ["$namespace\\Tests\\" => 'tests/']],
            'scripts' => [
                'post-autoload-dump' => '@php ./vendor/bin/testbench package:discover --ansi',
                'test' => 'vendor/bin/pest',
                'test-coverage' => 'vendor/bin/pest --coverage',
                'format' => 'vendor/bin/pint',
                'analyse' => 'vendor/bin/phpstan analyse',
            ],
            'config' => [
                'sort-packages' => true,
                'allow-plugins' => [
                    'pestphp/pest-plugin' => true,
                    'phpstan/extension-installer' => true,
                ],
            ],
            'extra' => [
                'laravel' => [
                    'providers' => ["$namespace\\$serviceProvider"],
                    'aliases' => [$facade => "$namespace\\Facades\\$facade"],
                ],
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
        ];

        $write('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
        $write('.editorconfig', Scaffold::editorconfig());
        $write('.gitattributes', Scaffold::gitattributes());
        $write('.gitignore', Scaffold::gitignore());
        $write('LICENSE.md', Scaffold::license($author, $year));
        $write('CHANGELOG.md', Scaffold::changelog());
        $write('phpstan.neon.dist', Scaffold::phpstanNeon());
        $write('phpunit.xml.dist', Scaffold::phpunitXml("$title Test Suite"));
        $write('README.md', Scaffold::readme($title, $vendor, $package, $description, "$vendor/$package"));

        $write('tests/TestCase.php', <<<PHP
        <?php

        namespace {$namespace}\Tests;

        use {$namespace}\\{$serviceProvider};
        use Orchestra\Testbench\TestCase as Orchestra;

        class TestCase extends Orchestra
        {
            protected function getPackageProviders(\$app): array
            {
                return [
                    {$serviceProvider}::class,
                ];
            }
        }

        PHP);

        $write('tests/Pest.php', <<<PHP
        <?php

        uses({$namespace}\Tests\TestCase::class)->in('Feature', 'Unit');

        PHP);

        $write('src/'.$serviceProvider.'.php', <<<PHP
        <?php

        namespace {$namespace};

        use Spatie\LaravelPackageTools\Package;
        use Spatie\LaravelPackageTools\PackageServiceProvider;

        class {$serviceProvider} extends PackageServiceProvider
        {
            public function configurePackage(Package \$package): void
            {
                \$package
                    ->name('{$package}')
                    ->hasConfigFile()
                    ->hasViews()
                    ->hasMigrations();
            }
        }

        PHP);

        $write('src/Facades/'.$facade.'.php', <<<PHP
        <?php

        namespace {$namespace}\Facades;

        use Illuminate\Support\Facades\Facade;

        /**
         * @see \\{$namespace}\\{$title}
         */
        class {$facade} extends Facade
        {
            protected static function getFacadeAccessor(): string
            {
                return '{$package}';
            }
        }

        PHP);

        $write("config/{$configFile}.php", "<?php\n\nreturn [\n];\n");

        $write('.github/workflows/tests.yml', <<<'YAML'
        name: Tests

        on:
          push:
            branches: [main]
          pull_request:
            branches: [main]

        jobs:
          test:
            runs-on: ubuntu-latest
            strategy:
              matrix:
                php: [8.4]
                laravel: ['13.*']
            name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }}
            steps:
              - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262 # v4
              - name: Setup PHP
                uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45 # v2
                with:
                  php-version: ${{ matrix.php }}
                  coverage: none
              - name: Install dependencies
                run: |
                  composer require "laravel/framework:${{ matrix.laravel }}" --no-interaction --no-update
                  composer update --prefer-dist --no-interaction
              - name: Run tests
                run: vendor/bin/pest

        YAML);

        $write('.github/workflows/pint.yml', Scaffold::workflowPint(['main']));
        $write('.github/workflows/phpstan.yml', Scaffold::workflowPhpstan(['main']));
        $write('.github/workflows/update-changelog.yml', Scaffold::workflowChangelog());

        $gitLog = [];
        if (! $noGit) {
            $gitLog[] = $this->git($dir, 'init -q', $dryRun);
            $gitLog[] = $this->git($dir, 'checkout -q -B main', $dryRun);
            $gitLog[] = $this->git($dir, 'add .', $dryRun);
            $gitLog[] = $this->git($dir, 'commit -q -m "chore: scaffold package structure"', $dryRun);
        }

        $this->components->info(($dryRun ? '[dry-run] ' : '')."Scaffolded {$vendor}/{$package} at {$dir}");
        foreach ($files as $line) {
            $this->line("  {$line}");
        }
        if (! $noGit) {
            $this->line('  git: '.implode(' | ', array_filter($gitLog)));
        }
        $this->newLine();
        $this->components->info('Next steps:');
        foreach ([
            'composer install',
            'vendor/bin/pest',
            'vendor/bin/phpstan analyse',
            'vendor/bin/pint',
            "gh repo create {$vendor}/{$package} --public --source={$dir} --remote=origin --push",
        ] as $step) {
            $this->line("  - {$step}");
        }

        return self::SUCCESS;
    }

    private function git(string $dir, string $cmd, bool $dryRun): string
    {
        if ($dryRun) {
            return "(dry-run) git {$cmd}";
        }
        File::ensureDirectoryExists($dir);
        $result = Process::path($dir)->run("git {$cmd}");
        if ($result->failed()) {
            return "git {$cmd} FAILED: ".trim($result->errorOutput());
        }

        return "git {$cmd} ok";
    }
}
