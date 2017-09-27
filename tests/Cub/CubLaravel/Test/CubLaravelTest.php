<?php namespace Cub\CubLaravel\Test;

use Cub;
use Firebase\JWT\JWT;
use Cub\CubLaravel\Exceptions\UserNotFoundByCubIdException;
use Cub\CubLaravel\Test\Models\User;

class CubLaravelTest extends CubLaravelTestCase
{
    /** @test */
    public function application_user_is_returned_from_login()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = $login->getUser();

        $this->assertInstanceOf($this->app['config']->get('cub::config.user'), $user);
        $this->assertEquals($user->email, $this->credentials['username']);
    }

    /**
     * @test
     * @expectedException \Cub\CubLaravel\Exceptions\UserNotFoundByCubIdException
     */
    public function exception_thrown_when_cub_user_is_not_application_user()
    {
        User::whereCubId($this->details['id'])->first()->delete();
        Cub::login($this->credentials['username'], $this->credentials['password']);
    }

    /** @test */
    public function application_user_is_returned_from_get_user_by_id()
    {
        $expected = User::whereCubId($this->details['id'])->first();
        $actual = Cub::getUserById($this->details['id']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException \Cub\CubLaravel\Exceptions\UserNotFoundByCubIdException
     */
    public function exception_thrown_when_no_cub_user_id()
    {
        Cub::getUserById('');
    }

    /** @test */
    public function no_cub_user_id_exception_method_is_descript()
    {
        $expected = 'User not found with Cub user id {empty_string}';
        $actual = '';
        try {
            Cub::getUserById('');
        } catch (UserNotFoundByCubIdException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function application_user_is_returned_from_get_user_by_jwt()
    {
        $expected = User::whereCubId($this->details['id'])->first();

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = Cub::getUserByJWT($jwt);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function exception_thrown_when_no_jwt()
    {
        Cub::getUserByJWT('');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function exception_thrown_when_bad_jwt()
    {
        Cub::getUserByJWT('kjashdkfjahkjashdfklaj');
    }
}
