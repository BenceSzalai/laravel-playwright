<?php

namespace didix16\LaravelPlaywright;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlaywrightBoilerplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'playwright:boilerplate {--ct=none : The Playwright package to use.}';

    /**
     * The console command description.
     */
    protected $description = 'Generate useful Playwright boilerplate.';

    /**
     * The path to the user's desired playwright install.
     */
    protected string $playwrightPath;

    /**
     * The Playwright packages to be used in the boilerplate.
     * @var array|string[]
     */
    protected array $playwrightPackages = [
        'none' => 'test',
        'react' => 'experimental-ct-react',
        'solid' => 'experimental-ct-solid',
        'vue' => 'experimental-ct-vue',
        'svelte' => 'experimental-ct-svelte',
    ];

    /**
     * Create a new Artisan command instance.
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Handle the command.
     */
    public function handle()
    {
        $this->promptIfNoOptionSet($this->input, $this->output);

        if (!$this->isPlaywrightInstalled()) {
            $this->requirePlaywrightInstall();

            return;
        }

        $playwrightPaths = [
            'e2e' => (int)$this->files->exists(base_path('e2e')),
            'tests/e2e' => (int)$this->files->exists(base_path('tests/e2e')),
            'playwright' => (int)$this->files->exists(base_path('playwright')),
            'tests/playwright' => (int)$this->files->exists(base_path('tests/playwright')),
        ];
        $playwrightPathDefault = array_flip($playwrightPaths)[true] ?? 'e2e';

        $this->playwrightPath = trim(
            strtolower($this->ask('Where should we put the playwright directory? It should be the same directory you choose in the playwright installation wizard', $playwrightPathDefault)),
            '/'
        );

        $this->copyStubs();
    }

    /**
     * Copy the stubs from this package to the user's playwright folder.
     */
    protected function copyStubs(): void
    {

        $this->files->copyDirectory(__DIR__ . '/stubs', $this->playwrightPath());
        // Replace stub template variables with user input
        $this->files->put(
            $this->playwrightPath('laravel-examples.spec.ts'),
            str_replace(
                '{{testPackage}}',
                $this->playwrightPackages[$this->option('ct')],
                $this->files->get($this->playwrightPath('laravel-examples.spec.ts'))
            )
        );

        $this->status('Writing', $this->playwrightPath('laravel-helpers.ts', false));
        $this->status('Writing', $this->playwrightPath('laravel-examples.spec.ts', false));

        $this->line('');
    }

    /**
     * Get the user-requested path to the Playwright directory.
     */
    protected function playwrightPath(string $path = '', bool $absolute = true): string
    {
        $playwrightPath = $absolute ? base_path($this->playwrightPath) : $this->playwrightPath;

        return $playwrightPath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Report the status of a file to the user.
     */
    protected function status(string $type, string $file)
    {
        $this->line("<info>{$type}</info> <comment>{$file}</comment>");
    }

    /**
     * Require that the user first install playwright through npm.
     */
    protected function requirePlaywrightInstall()
    {
        $withPackages = $this->option('ct') !== 'none';
        $useCtNpm = $withPackages ? ' -- --ct' : '';
        $useCt = $withPackages ? ' --ct' : '';
        $withPackages = $withPackages ? 'with ' . $this->playwrightPackages[$this->option('ct')] . ' test components' : '';

        $installPlaywright = $withPackages ? "\nnpm i -D @playwright/test\nyarn add -D @playwright/test\npnpm add -D @playwright/test" : '';

        $this->warn(
            <<<EOT

Playwright $withPackages not found. Please install it through your node package manager and try again.

npm init playwright@latest$useCtNpm
yarn create playwright$useCt
pnpm create playwright$useCt
$installPlaywright
EOT
        );
    }

    /**
     * Check if Playwright is added to the package.json file.
     */
    protected function isPlaywrightInstalled(): bool
    {
        $package = json_decode($this->files->get(base_path('package.json')), true);
        $playwrightPackage = $this->playwrightPackages[$this->option('ct')];

        // return true if the package.json file contains the @playwright/test package
        // if $playwrightPackage is not 'test' then also check for the specific package

        return $playwrightPackage === 'test' ?
            (
                Arr::has($package, 'devDependencies.@playwright/test') ||
                Arr::has($package, 'dependencies.@playwright/test')
            ) :
            (
                Arr::has($package, 'devDependencies.@playwright/' . $playwrightPackage) ||
                Arr::has($package, 'dependencies.@playwright/' . $playwrightPackage)
            ) &&
            (
                Arr::has($package, 'devDependencies.@playwright/test') ||
                Arr::has($package, 'dependencies.@playwright/test')
            );
    }

    protected function promptIfNoOptionSet(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('ct') !== 'none') {
            return;
        }
        $input->setOption('ct', $this->choice('Do you use some component library and want to test components with playwright?',
            ['none', 'react', 'solid', 'vue', 'svelte'],
            'none'
        ));
    }
}
