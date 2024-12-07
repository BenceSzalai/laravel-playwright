# Laravel + Playwright Integration

This package provides the necessary boilerplate to quickly begin testing your Laravel applications using Playwright.

## Installation

If you haven't already installed [Playwright](https://playwright.dev/docs/intro); that's your first step.

```bash
yarn create playwright
```
If you want to use also the test components, you can add the `--ct` flag to the command above.

```bash
yarn create playwright --ct
```

Now you're ready to install this package through Composer. Pull it in as a development-only dependency.

```bash
composer require didix16/laravel-playwright --dev
```

Finally, run the `playwright:boilerplate` command to copy over the initial boilerplate files for your Playwright tests.

```bash
php artisan playwright:boilerplate
```

Also, you can run the command with the `--ct` option to copy the boilerplate for the test components.

```bash
php artisan playwright:boilerplate --ct={none|react|solid|vue|svelte}
```

## Configuration
In order to make it work, you have to edit the `playwright-ct.config.ts` or `playwright.config.ts` file and set the following properties:

```ts
testDir: './tests/playwright', // or whatever your Playwright test directory is
workers: 1, // set it to 1 to avoid database collisions
use: {
    baseURL: 'http://localhost:8000', // or whatever your Laravel test app URL is
},
```
}

That's it! You're ready to go. We've provided an `laravel-examples.spec.ts` spec for you to play around with it. Let's run it now:

```
yarn playwright test
```

> [!WARNING]
> The `laravel-examples.spec.ts` spec calls `php artisan migrate:fresh --seed` as one of the examples, so if you have important information in your local database make sure to either remove the [`Can execute arbitrary PHP`](./src/stubs/laravel-examples.spec.ts#L25) test first or switch the database (e.g. set a different `DB_CONNECTION` in your `.env`)

## TODO
 - [ ] Make the tests can run in parallel to avoid database collisions

## Credits

- [Yoann Frommelt](https://www.linkedin.com/in/yoannfrommelt/)
- [Jeffrey Way](https://twitter.com/jeffrey_way) for the amazing inspiration

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
