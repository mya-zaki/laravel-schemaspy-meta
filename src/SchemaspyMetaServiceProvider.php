<?php
namespace MyaZaki\LaravelSchemaspyMeta;

use MyaZaki\LaravelSchemaspyMeta\Console\GenerateSchemaMetaCommand;
use Illuminate\Support\ServiceProvider;

class SchemaspyMetaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'command.schemaspy-meta:generate',
            function ($app) {
                return new GenerateSchemaMetaCommand();
            }
        );

        $this->commands(
            'command.schemaspy-meta:generate'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('command.schemaspy-meta:generate');
    }
}
