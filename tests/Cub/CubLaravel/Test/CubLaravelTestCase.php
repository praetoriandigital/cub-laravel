<?php namespace Cub\CubLaravel\Test;

use Cub;
use Cub\CubLaravel\Contracts\CubGateway;
use Cub\CubLaravel\Support\FakeCubApiGateway;
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

        $this->app->bind(CubGateway::class, FakeCubApiGateway::class);
        $this->modifyConfiguration($this->app);
        $this->prepareDatabase();
        $this->prepareRoutes();
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
     * Perform user specific configuration.
     */
    protected function modifyConfiguration($app)
    {
        $app['config']->set('cub::config.maps.user.model', 'Cub\CubLaravel\Test\Models\User');
        $app['config']->set('cub::config.maps.user.transformer', 'Cub\CubLaravel\Transformers\CubObjectTransformer');
        $app['config']->set('cub::config.maps.organization.model', 'Cub\CubLaravel\Test\Models\Organization');
        $app['config']->set('cub::config.maps.member.model', 'Cub\CubLaravel\Test\Models\Member');
        $app['config']->set('cub::config.maps.group.model', null);
        $app['config']->set('cub::config.maps.groupmember.model', 'Cub\CubLaravel\Test\Models\GroupMember');
        $app['config']->set('cub::config.maps.country.model', 'Cub\CubLaravel\Test\Models\Country');
        $app['config']->set('cub::config.maps.state.model', 'Cub\CubLaravel\Test\Models\State');
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
        DB::table('organizations')->where('id', 1)->update(['cub_id' => 'org_jhakjhwk4esjkjahs']);
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
}
