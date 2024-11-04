<?php

namespace didix16\LaravelPlaywright;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlaywrightServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // If the app is not running in the testing environment, DON'T register the package
        if (!$this->app->environment(env('PLAYWRIGHT_ENV', 'testing'), 'local')) {
            return;
        }

        $this->addRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/routes/playwright.php' => base_path('routes/playwright.php'),
            ]);

            $this->commands([
                PlaywrightBoilerplateCommand::class,
            ]);
        }
    }

    protected function addRoutes()
    {
        Route::namespace('')
            ->middleware('web')
            ->group(__DIR__.'/routes/playwright.php');
    }
}
