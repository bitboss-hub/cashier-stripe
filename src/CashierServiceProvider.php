<?php

namespace BitbossHub\Cashier;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use BitbossHub\Cashier\Console\WebhookCommand;
use BitbossHub\Cashier\Contracts\InvoiceRenderer;
use BitbossHub\Cashier\Invoices\DompdfInvoiceRenderer;
use Stripe\Stripe;
use Stripe\Util\LoggerInterface;

class CashierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->registerLogger();
        $this->registerRoutes();
//        $this->registerResources();
        $this->registerPublishing();
//        $this->registerCommands();

        Stripe::setAppInfo(
            'BitbossHub Cashier',
            Cashier::VERSION,
            'https://laravel.com'
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
//        $this->bindLogger();
//        $this->bindInvoiceRenderer();
    }

    /**
     * Setup the configuration for Cashier.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cashier.php', 'cashier'
        );
    }

    /**
     * Bind the Stripe logger interface to the Cashier logger.
     *
     * @return void
     */
    protected function bindLogger()
    {
        $this->app->bind(LoggerInterface::class, function ($app) {
            return new Logger(
                $app->make('log')->channel(config('cashier.logger'))
            );
        });
    }

    /**
     * Bind the default invoice renderer.
     *
     * @return void
     */
    protected function bindInvoiceRenderer()
    {
        $this->app->bind(InvoiceRenderer::class, function ($app) {
            return $app->make(config('cashier.invoices.renderer', DompdfInvoiceRenderer::class));
        });
    }

    /**
     * Register the Stripe logger.
     *
     * @return void
     */
    protected function registerLogger()
    {
        if (config('cashier.logger')) {
            Stripe::setLogger($this->app->make(LoggerInterface::class));
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Cashier::$registersRoutes) {
            Route::group([
                'prefix' => config('cashier.path'),
                'namespace' => 'BitbossHub\Cashier\Http\Controllers',
                'as' => 'cashier.',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }
    }

    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cashier');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cashier.php' => $this->app->configPath('cashier.php'),
            ], 'cashier-config');

            $publishesMigrationsMethod = method_exists($this, 'publishesMigrations')
                ? 'publishesMigrations'
                : 'publishes';

            $this->{$publishesMigrationsMethod}([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'cashier-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/cashier'),
            ], 'cashier-views');
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookCommand::class,
            ]);
        }
    }
}
