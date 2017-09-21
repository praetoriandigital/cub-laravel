<?php namespace Praetoriandigital\CubLaravel\Test;

use DB;
use Cub_Config;
use Orchestra\Testbench\TestCase;

/**
*
*/
abstract class CubLaravelTestCase extends TestCase
{
    public $details;
    public $credentials;
  
    public function setUp()
    {
        parent::setUp();

        $this->app['path.base'] = __DIR__ . '/../src';
        $artisan = $this->app->make('artisan');

        $this->details = [
        'original_username' => 'ivelum',
        'first_name' => 'do not remove of modify',
        'last_name' => 'user for tests',
        ];
        $this->credentials = [
        'username' => 'support@ivelum.com',
        'password' => 'SJW8Gg',
        ];

        $artisan->call('migrate', [
        '--database' => 'testbench',
        '--path'     => '../tests/migrations',
        ]);

        $artisan->call('migrate', [
        '--database' => 'testbench',
        '--path'     => '/migrations',
        ]);

      // update our user to have a correct cub_id
        DB::table('users')->where('id', 1)->update(['cub_id' => 'usr_upfrcJvCTyXCVBj8']);
    }

    protected function getPackageProviders()
    {
        return ['Praetoriandigital\CubLaravel\Providers\CubLaravelServiceProvider'];
    }

    protected function getPackageAliases()
    {
        return [
          'Cub' => 'Praetoriandigital\CubLaravel\Facades\CubLaravel',
        ];
    }

  /**
   * Define environment setup.
   *
   * @param  \Illuminate\Foundation\Application  $app
   * @return void
   */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
          'driver'   => 'sqlite',
          'database' => ':memory:',
          'prefix'   => '',
        ]);

        $app['config']->set('cub.public_key', getEnv('CUB_PUBLIC'));
        $app['config']->set('cub.secret_key', getEnv('CUB_SECRET'));
        $app['config']->set('cub.api_url', getEnv('CUB_API_URL'));
        $app['config']->set('cub.webhook_url', getEnv('CUB_WEBHOOK_URL'));
        $app['config']->set('cub.provider.user', 'Praetoriandigital\CubLaravel\Providers\User\EloquentUserAdapter');
        $app['config']->set('cub.user', 'Praetoriandigital\CubLaravel\Test\Models\User');
    }
}
