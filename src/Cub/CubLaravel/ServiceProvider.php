<?php namespace Cub\CubLaravel;

use Cub_Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('cub/cub-laravel', 'cub');

        // register the filter
        $this->app['router']->filter('cub-auth', 'pd.cub.auth-filter');

        include __DIR__ . '/routes.php';

        Cub_Config::$api_key = $this->app['config']->get('cub::config.secret_key');
        Cub_Config::$api_url = $this->app['config']->get('cub::config.api_url');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('pd.cub.auth-filter', function ($app) {
            return new CubAuthFilter($app->make('cub'));
        });

        $this->app->bind('cub', function ($app) {
            return new Cub(new CubObjectTransformer, $app['request']);
        });

        $this->app->bind('cub-widget', function ($app) {
            return new CubWidget;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
