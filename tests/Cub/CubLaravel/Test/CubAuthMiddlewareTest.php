<?php namespace Cub\CubLaravel\Test;

use Cub;
use Cub\CubLaravel\Test\Models\User;
use Firebase\JWT\JWT;

class CubAuthMiddlewareTest extends CubLaravelTestCase
{
    public function testRequestWithValidToken()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Hello, Cub User '.$this->details['id']]),
        ];

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithValidTokenHeader()
    {
        $expected = [
            'code' => 200,
            'content' => json_encode(['message' => 'Hello, Cub User '.$this->details['id']]),
        ];

        $login = Cub::login($this->credentials['username'], $this->credentials['password']);
        $jwt = $login->getToken();

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestNeedsToken()
    {
        $expected = [
            'code' => 400,
            'content' => json_encode(['error' => 'token_not_provided']),
        ];

        $actual = $this->get('restricted');

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithExpiredToken()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $jwt = $this->getToken([
            'exp' => time() - 5000,
        ]);

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithExpiredTokenHeader()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'expired_token']),
        ];

        $jwt = $this->getToken([
            'exp' => time() - 5000,
        ]);

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithTokenNotYetValid()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $jwt = $this->getToken([
            'nbf' => time() + 5000,
        ]);

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithTokenNotYetValidHeader()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'token_not_yet_valid']),
        ];

        $jwt = $this->getToken([
            'nbf' => time() + 5000,
        ]);

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithNonExistentUser()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $jwt = $this->getToken();

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithNonExistentUserHeader()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 404,
            'content' => json_encode(['error' => 'user_not_found']),
        ];

        $jwt = $this->getToken();

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithTokenWithScopeClaim()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'invalid_token']),
        ];

        $jwt = $this->getToken([
            'scope' => 'fail',
        ]);

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithTokenWithScopeClaimHeader()
    {
        $expected = [
            'code' => 401,
            'content' => json_encode(['error' => 'invalid_token']),
        ];

        $jwt = $this->getToken([
            'scope' => 'wat',
        ]);

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithBadToken()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!', 'HS256');

        $actual = $this->get('restricted?cub_token='.$jwt);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }

    public function testRequestWithBadTokenHeader()
    {
        User::whereCubId($this->details['id'])->first()->delete();

        $expected = [
            'code' => 500,
            'content' => json_encode(['error' => 'error_processing_token']),
        ];

        $token = [
            'user' => $this->details['id'],
        ];
        $jwt = JWT::encode($token, 'giveme500!', 'HS256');

        $actual = $this->get('restricted', ['HTTP_Authorization' => 'Bearer '.$jwt]);

        self::assertEquals($expected['code'], $actual->getStatusCode());
        self::assertEquals($expected['content'], $actual->getContent());
    }
}
