<?php

namespace Cub\CubLaravel\Test;

use Cub\CubLaravel\Contracts\CubLogin;
use Cub_Unauthorized;
use Cub_User;
use Firebase\JWT\JWT;

class FakeCubLogin implements CubLogin
{
    /**
     * @param $username
     * @param $password
     * @return Cub_User
     */
    public function login($username, $password)
    {
        if ($username !== 'support@ivelum.com' || $password !== 'SJW8Gg') {
            throw new Cub_Unauthorized();
        }

        return new Cub_User([
            'id' => 'usr_upfrcJvCTyXCVBj8',
            'token' => JWT::encode([
                'user' => 'usr_upfrcJvCTyXCVBj8',
            ], config('cub.secret_key'), 'HS256'),
        ]);
    }
}