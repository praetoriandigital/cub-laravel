<?php

namespace Cub\CubLaravel\Contracts;

use Cub_User;

interface CubLogin
{
    /**
     * @param $username
     * @param $password
     * @return Cub_User
     */
    public function login($username, $password);
}