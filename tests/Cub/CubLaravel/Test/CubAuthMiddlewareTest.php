<?php namespace Cub\CubLaravel\Test;

use Cub;
use Cub\CubLaravel\Test\Models\User;
use Firebase\JWT\JWT;

class CubAuthMiddlewareTest extends CubLaravelTestCase
{
    /** @test */
    public function request_with_valid_token()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Hello, Cub User '.$this->details['id']]),
        ];

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_valid_token_header()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Hello, Cub User '.$this->details['id']]),
        ];

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_needs_token()
    {
        $expected = [
            'code' => 400,
            'content' => json_encode(['error' => 'token_not_provided']),
        ];

        $actual = $this->get('restricted');

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_expired_token()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $token = [
            'exp' => time() - 5000,
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_expired_token_header()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $token = [
            'exp' => time() - 5000,
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_token_not_yet_valid()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $token = [
            'nbf' => time() + 5000,
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_token_not_yet_valid_header()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $token = [
            'nbf' => time() + 5000,
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_non_existent_user()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_non_existent_user_header()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_token_with_scope_claim()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'invalid_token']),
        ];

        $token = [
            'user' => $this->details['id'],
            'scope' => 'fail',
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_token_with_scope_claim_header()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'invalid_token']),
        ];

        $token = [
            'user' => $this->details['id'],
            'scope' => 'wat',
        ];
        $jwt = JWT::encode($token, config('cub.secret_key'));

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_bad_token()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!');

        $actual = $this->get('restricted?cub_token='.$jwt);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }

    /** @test */
    public function request_with_bad_token_header()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!');

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        $this->assertEquals($expected['code'], $actual->getStatusCode());
        $this->assertEquals($expected['content'], $actual->getContent());
    }
}
