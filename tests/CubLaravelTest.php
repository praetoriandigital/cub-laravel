<?php namespace Praetoriandigital\CubLaravel\Test;

use Cub;
use Config;
use Praetoriandigital\CubLaravel\Exceptions\UserNotFoundException;

class CubLaravelTest extends CubLaravelTestCase
{
    /** @test */
    public function applicationUserIsReturnedFromLogin()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = $login->getUser();

        $this->assertInstanceOf(Config::get('cub.user'), $user);
        $this->assertEquals($user->email, $this->credentials['username']);
    }

    /**
     * @test
     * @expectedException \Praetoriandigital\CubLaravel\Exceptions\UserNotFoundException
     */
    public function exceptionThrownWhenNoCubId()
    {
        Cub::getUserByCubId('');
    }

    /** @test */
    public function exceptionMethodIsDescript()
    {
        $expected = 'User not found with cub_id {empty_string}';
        $actual = '';
        try {
            Cub::getUserByCubId('');
        } catch (UserNotFoundException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function webhookUrlIsRegistered()
    {
      // dd($this->app['router']);
    }
}
