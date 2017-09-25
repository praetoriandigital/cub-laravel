<?php namespace Praetoriandigital\CubLaravel;

use Config;
use Cub_User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Praetoriandigital\CubLaravel\Exceptions\NoJWTOnRequestException;
use Praetoriandigital\CubLaravel\Exceptions\UserNotFoundByCubIdException;

class Cub
{
    const ALGO = 'HS256';
    const CUB_ID_KEY = 'user';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Cub constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Model $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
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
     * @param null $token
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUserByJWT($token = null)
    {
        if (!$token) {
            $token = $this->getRequestJWT();
        }

        $decoded = (array) JWT::decode($token, Config::get('cub.secret_key'), [self::ALGO]);

        return $this->getUserById($decoded[self::CUB_ID_KEY]);
    }

    /**
     * Check if a valid Cub JWT exists on the request
     *
     * @return bool
     */
    public function validJWTExists()
    {
        try {
            $jwt = $this->getRequestJWT();
        } catch (NoJWTOnRequestException $e) {
            return false;
        }

        try {
            $decoded = (array) JWT::decode($jwt, Config::get('cub.secret_key'), [self::ALGO]);
        } catch (\Exception $e) {
            return false;
        }

        return array_key_exists(self::CUB_ID_KEY, $decoded);
    }

    /**
     * @param string $query
     *
     * @return string
     * @throws NoJWTOnRequestException
     */
    public function getRequestJWT($query = 'cub_token')
    {
        if (!$token = $this->parseAuthHeader()) {
            if (!$token = $this->request->query($query, false)) {
                throw new NoJWTOnRequestException();
            }
        }

        return $token;
    }

    /**
     * Parse token from the authorization header
     *
     * @param string  $header
     * @param string  $method
     * @return false|string
     */
    protected function parseAuthHeader($header = 'authorization', $method = 'bearer')
    {
        $header = $this->request->headers->get($header);

        if (! starts_with(strtolower($header), $method)) {
            return false;
        }

        return trim(str_ireplace($method, '', $header));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appUser
     * @param Cub_User $cubUser
     *
     * @return void
     */
    public function updateUser(Model $appUser, Cub_User $cubUser)
    {
        $fields = Config::get('cub.fields');
        if (is_array($fields)) {
            $updates = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, array_keys($appUser['attributes']))) {
                    $updates[$appField] = $cubUser->{$cubField};
                }
            }
            if (count($updates)) {
                $appUser->update($updates);
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appUser
     *
     * @return void
     */
    public function deleteUser(Model $appUser)
    {
        $appUser->delete();
    }
}
