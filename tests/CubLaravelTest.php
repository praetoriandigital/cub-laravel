<?php namespace Praetoriandigital\CubLaravel\Test;

use Cub;
use Config;
use Firebase\JWT\JWT;
use Praetoriandigital\CubLaravel\Exceptions\UserNotFoundByCubIdException;
use Praetoriandigital\CubLaravel\Test\Models\User;

class CubLaravelTest extends CubLaravelTestCase
{
    /** @test */
    public function application_user_is_returned_from_login()
    {
        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $user = $login->getUser();

        $this->assertInstanceOf(Config::get('cub.user'), $user);
        $this->assertEquals($user->email, $this->credentials['username']);
    }

    /**
     * @test
     * @expectedException \Praetoriandigital\CubLaravel\Exceptions\UserNotFoundByCubIdException
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
     * @expectedException \Praetoriandigital\CubLaravel\Exceptions\UserNotFoundByCubIdException
     */
    public function exception_thrown_when_no_cub_id()
    {
        Cub::getUserById('');
    }

    /** @test */
    public function no_cub_id_exception_method_is_descript()
    {
        $expected = 'User not found with cub_id {empty_string}';
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

        $token = [
            'cub_id' => $expected->cub_id,
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

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

    /** @test */
    public function webhook_url_is_registered()
    {
        $this->call('POST', Config::get('cub.webhook_url'), [
            'payload' => json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'username' => '',
                'deleted' => false,
            ]),
        ]);
    }

    /** @test */
    public function updated_cub_user_updates_application_user()
    {
        $expectedFirstName = 'Luke';
        $expectedLastName = 'Skywalker';
        $expectedEmail = 'luke@lukeskywalker.com';
        $expectedUsername = 'lukie1';

        $this->call('POST', Config::get('cub.webhook_url'), [
            'payload' => json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => $expectedFirstName,
                'last_name' => $expectedLastName,
                'email' => $expectedEmail,
                'username' => $expectedUsername,
                'deleted' => false,
            ]),
        ]);

        $user = User::whereCubId($this->details['id'])->first();
        $this->assertEquals($expectedFirstName, $user->first_name);
        $this->assertEquals($expectedLastName, $user->last_name);
        $this->assertEquals($expectedEmail, $user->email);
        $this->assertEquals($expectedUsername, $user->username);
    }

    /** @test */
    public function deleted_cub_user_deletes_application_user()
    {
        $this->call('POST', Config::get('cub.webhook_url'), [
            'payload' => json_encode([
                'object' => 'User',
                'id' => $this->details['id'],
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'username' => '',
                'deleted' => true,
            ]),
        ]);

        $user = User::whereCubId($this->details['id'])->first();
        $this->assertNull($user);
    }

    /** @test */
    public function cub_auth_filter_with_valid_token()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Right on!']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', ['cub_token' => $jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_valid_token_header()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Right on!']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', [], [], ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_needs_token()
    {
        $expected = [
            'code' => 400,
            'content' => json_encode(['error' => 'token_not_provided']),
        ];

        $actual = $this->call('GET', 'restricted');

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_expired_token()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $token = [
            'exp' => time() - 5000,
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', ['cub_token' => $jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_expired_token_header()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $token = [
            'exp' => time() - 5000,
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', [], [], ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_token_not_yet_valid()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $token = [
            'nbf' => time() + 5000,
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', ['cub_token' => $jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_token_not_yet_valid_header()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $token = [
            'nbf' => time() + 5000,
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', [], [], ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_non_existent_user()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', ['cub_token' => $jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_non_existent_user_header()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, Config::get('cub.secret_key'));

        $actual = $this->call('GET', 'restricted', [], [], ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_bad_token()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!');

        $actual = $this->call('GET', 'restricted', ['cub_token' => $jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function cub_auth_filter_with_bad_token_header()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'cub_id' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!');

        $actual = $this->call('GET', 'restricted', [], [], ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }
}
