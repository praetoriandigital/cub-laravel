<?php

namespace Cub\CubLaravel\Support;

use Cub\CubLaravel\Contracts\CubLogin;
use Cub_User;

class LoginService implements CubLogin
{
    public function login($username, $password)
    {
        return Cub_User::login($username, $password);
    }
}