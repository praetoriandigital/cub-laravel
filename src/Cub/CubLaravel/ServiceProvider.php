<?php namespace Cub\CubLaravel;

use Cub\CubLaravel\Contracts\CubGateway;
use Cub\CubLaravel\Contracts\CubLogin;
use Cub\CubLaravel\Cub;
use Cub\CubLaravel\CubWidget;
use Cub\CubLaravel\Middleware\CubAuthMiddleware;
use Cub\CubLaravel\Support\CubApiGateway;
use Cub\CubLaravel\Support\LoginService;
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

    public $middleware = [
        'cub-auth' => CubAuthMiddleware::class,
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/cub.php' => config_path('cub.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        Cub_Config::$api_key = config('cub.secret_key');
        Cub_Config::$api_url = config('cub.api_url');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/cub.php', 'cub');

//        $this->app->singleton('cub.auth-middleware', function ($app) {
//            return new CubAuthMiddleware($app->make('cub'));
//        });

        $this->app->bind(CubGateway::class, CubApiGateway::class);
        $this->app->bind(CubLogin::class, LoginService::class);
        $this->app->bind('cub', Cub::class);
        $this->app->bind('cub-widget', CubWidget::class);
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
