<?php namespace Praetoriandigital\CubLaravel;

use Cub_User;
use Illuminate\Database\Eloquent\Model;
use Praetoriandigital\CubLaravel\Exceptions\UserNotFoundException;

class Cub
{
    /**
     * Cub constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     */
    public function __construct(Model $user)
    {
        $this->user = $user;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return Login
     * @throws UserNotFoundException
     */
    public function login($username, $password)
    {
        $cub_user = Cub_User::login($username, $password);
        $user = $this->user->whereCubId($cub_user->id)->first();
        if (!$user) {
            throw new UserNotFoundException($cub_user->id);
        }
        return new Login($user, $cub_user->token);
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws UserNotFoundException
     */
    public function getUserByCubId($cubId)
    {
        $user = $this->user->whereCubId($cubId)->first();
        if (!$user) {
            throw new UserNotFoundException($cubId);
        }
        return $user;
    }
}
