<?php namespace Praetoriandigital\CubLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Config;
use Cub_Config;
use Praetoriandigital\CubLaravel\Cub;

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

        include __DIR__.'/../../../routes.php';

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
        $this->app->singleton('Praetoriandigital\CubLaravel\Providers\User\UserInterface', function ($app) {
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

        $this->app->bind('cub', function ($app) {
            return new Cub($app->make(Config::get('cub.user')));
        });
    }

    /**
     * Register the bindings for the User provider
     */
    protected function registerUserProvider()
    {
        $this->app->singleton('pd.cub.provider.user', function ($app) {
            $provider = Config::get('cub.provider.user');
            $model = $app->make(Config::get('cub.user'));
            return new $provider($model);
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
