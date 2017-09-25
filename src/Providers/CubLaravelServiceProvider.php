<?php namespace Cub\CubLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Config;
use Cub_Config;
use Cub\CubLaravel\Cub;
use Cub\CubLaravel\CubAuthFilter;

class CubLaravelServiceProvider extends ServiceProvider
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
        $this->package('praetoriandigital/cub-laravel', 'cub');

        $this->bootBindings();

        // register the filter
        $this->app['router']->filter('cub-auth', 'pd.cub.auth-filter');

        include __DIR__.'/../routes.php';

        Cub_Config::$api_key = Config::get('cub.secret_key');
        Cub_Config::$api_url = Config::get('cub.api_url');
    }

    /**
     * Register the bindings for the application.
     *
     * @return void
     */
    protected function bootBindings()
    {
        $this->app->singleton('Cub\CubLaravel\Providers\User\UserInterface', function ($app) {
            return $app['pd.cub.provider.user'];
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerUserProvider();
        $this->registerCubAuthFilter();

        $this->app->bind('cub', function ($app) {
            return new Cub($app->make(Config::get('cub.user')), $app['request']);
        });
    }

    /**
     * Register the bindings for the User provider
     */
    protected function registerUserProvider()
    {
        $this->app->singleton('pd.cub.provider.user', function ($app) {
            $provider = 'Cub\CubLaravel\Providers\User\EloquentUserAdapter';
            $model = $app->make(Config::get('cub.user'));
            return new $provider($model);
        });
    }

    /**
     * Register the bindings for the 'cub-auth' filter
     */
    protected function registerCubAuthFilter()
    {
        $this->app->singleton('pd.cub.auth-filter', function ($app) {
            return new CubAuthFilter($app->make('cub'));
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
