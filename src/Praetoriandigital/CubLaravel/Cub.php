<?php namespace Praetoriandigital\CubLaravel;

use Config;
use Cub_User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Praetoriandigital\CubLaravel\Exceptions\UserNotFoundByCubIdException;

class Cub
{
    const ALGO = 'HS256';
    const CUB_ID_KEY = 'cub_id';

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
     */
    public function login($username, $password)
    {
        $cub_user = Cub_User::login($username, $password);
        $user = $this->getUserById($cub_user->id);
        return new Login($user, $cub_user->token);
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws UserNotFoundByCubIdException
     */
    public function getUserById($cubId)
    {
        $user = $this->user->whereCubId($cubId)->first();
        if (!$user) {
            throw new UserNotFoundByCubIdException($cubId);
        }
        return $user;
    }

    /**
     * @param $token
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUserByJWT($token)
    {
        $decoded = (array) JWT::decode($token, Config::get('cub.secret_key'), [self::ALGO]);

        return $this->getUserById($decoded[self::CUB_ID_KEY]);
    }
}
