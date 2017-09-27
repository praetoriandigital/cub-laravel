<?php namespace Cub\CubLaravel\Test;

use Cub;
use DB;
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

        $this->app['path.base'] = __DIR__ . '/../../../../src';

        $this->details = [
            'original_username' => 'ivelum',
            'first_name' => 'do not remove of modify',
            'last_name' => 'user for tests',
            'id' => 'usr_upfrcJvCTyXCVBj8',
        ];
        $this->credentials = [
            'username' => 'support@ivelum.com',
            'password' => 'SJW8Gg',
        ];

        $this->prepareDatabase();
        $this->prepareRoutes();
        $this->modifyConfiguration($this->app);
    }

    /**
     * @return array
     */
    protected function getPackageProviders()
    {
        return ['Cub\CubLaravel\ServiceProvider'];
    }

    /**
     * @return array
     */
    protected function getPackageAliases()
    {
        return [
          'Cub' => 'Cub\CubLaravel\Facades\Cub',
          'CubWidget' => 'Cub\CubLaravel\Facades\CubWidget',
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
    }

    /**
     * Migrate the database and update seeded data.
     */
    protected function prepareDatabase()
    {
        $artisan = $this->app->make('artisan');

        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--path'     => '../tests/Cub/CubLaravel/Test/migrations',
        ]);

        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--path'     => '/migrations',
        ]);

        // Update our user to have a correct cub_id
        DB::table('users')->where('id', 1)->update(['cub_id' => $this->details['id']]);
    }

    /**
     * Prepare routes.
     */
    protected function prepareRoutes()
    {
        $this->app['router']->get('restricted', ['before' => 'cub-auth', function () {
            return json_encode(['message' => 'Hello, Cub User '.Cub::currentUser()->cub_id]);
        }]);

        $this->app['router']->enableFilters();
    }

    /**
     * Perform user specific configuration.
     */
    protected function modifyConfiguration($app)
    {
        $app['config']->set('cub::config.user', 'Cub\CubLaravel\Test\Models\User');
    }
}
