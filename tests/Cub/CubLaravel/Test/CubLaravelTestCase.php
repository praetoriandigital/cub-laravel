<?php namespace Cub\CubLaravel\Test;

use Cub\CubLaravel\Contracts\CubGateway;
use Cub\CubLaravel\Contracts\CubLogin;
use Cub\CubLaravel\Cub;
use Cub\CubLaravel\Middleware\CubAuthMiddleware;
use DB;
use Firebase\JWT\JWT;
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
        $this->app->bind(CubLogin::class, FakeCubApiGateway::class);
        $this->modifyConfiguration();
        $this->prepareDatabase();
        $this->prepareRoutes();
    }

    /**
     * @param $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Cub\CubLaravel\ServiceProvider'];
    }

    /**
     * @param $app
     * @return array
     */
    protected function getPackageAliases($app)
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
        $app->setBasePath(__DIR__ . '/../../../../src');
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
    protected function modifyConfiguration()
    {
        config(['cub.maps.user.model' => 'Cub\CubLaravel\Test\Models\User']);
        config(['cub.maps.user.transformer' => 'Cub\CubLaravel\Transformers\CubObjectTransformer']);
        config(['cub.maps.organization.model' => 'Cub\CubLaravel\Test\Models\Organization']);
        config(['cub.maps.member.model' => 'Cub\CubLaravel\Test\Models\Member']);
        config(['cub.maps.group.model' => null]);
        config(['cub.maps.groupmember.model' => 'Cub\CubLaravel\Test\Models\GroupMember']);
        config(['cub.maps.country.model' => 'Cub\CubLaravel\Test\Models\Country']);
        config(['cub.maps.state.model' => 'Cub\CubLaravel\Test\Models\State']);
    }

    /**
     * Migrate the database and update seeded data.
     */
    protected function prepareDatabase()
    {
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--path'     => '../tests/Cub/CubLaravel/Test/migrations',
        ]);

        $this->artisan('migrate', [
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
        app('router')->aliasMiddleware('cub-auth', CubAuthMiddleware::class);

        app('router')->get('restricted', ['middleware' => 'cub-auth', function () {
            return response()->json(['message' => 'Hello, Cub User '.Cub::currentUser()->cub_id]);
        }]);
    }

    protected function getToken(array $payload = [])
    {
        return JWT::encode(array_merge([
            Cub::CUB_ID_KEY => $this->details['id'],
        ], $payload), config('cub.secret_key'));
    }
}
