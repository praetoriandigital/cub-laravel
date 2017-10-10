<?php namespace Cub\CubLaravel;

use Carbon\Carbon;
use Config;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub_Object;
use Cub_User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cub
{
    const ALGO = 'HS256';
    const CUB_ID_KEY = 'user';
    const CUB_COOKIE = 'cubUserToken';

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    private $currentUser = null;

    private $currentToken = null;

    /**
     * Cub constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
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

        $this->setCurrentUser($user);
        $this->setCurrentToken($cub_user->token);

        return new Login($user, $cub_user->token);
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->setCurrent();
    }

    /**
     * @return bool
     */
    public function check()
    {
        return (bool) $this->currentUser();
    }    

    /**
     * @param \Illuminate\Database\Eloquent\Model|null $user
     * @param string|null $token
     *
     * @return void
     */
    private function setCurrent(Model $user = null, $token = null)
    {
        $this->setCurrentUser($user);
        $this->setCurrentToken($token);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|null $user
     *
     * @return void
     */
    private function setCurrentUser(Model $user = null)
    {
        $this->currentUser = $user;
    }

    /**
     * @param string|null $token
     *
     * @return void
     */
    private function setCurrentToken($token = null)
    {
        $this->currentToken = $token;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function currentUser()
    {
        return $this->currentUser;
    }

    /**
     * @return string|null
     */
    public function currentToken()
    {
        return $this->currentToken;
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUserById($cubId)
    {
        return $this->getObjectById(strtolower(Cub_User::class), $cubId);
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws ObjectNotFoundByCubIdException
     */
    public function getObjectById($objectType, $cubId)
    {
        $object = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'))->whereCubId($cubId)->first();
        if (!$object) {
            throw new ObjectNotFoundByCubIdException($cubId);
        }
        return $object;
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

        $decoded = (array) JWT::decode($token, Config::get('cub::config.secret_key'), [self::ALGO]);

        $this->setCurrent($this->getObjectById(strtolower(Cub_User::class), $decoded[self::CUB_ID_KEY]), $token);

        return $this->currentUser();
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
            $decoded = (array) JWT::decode($jwt, Config::get('cub::config.secret_key'), [self::ALGO]);
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
            if (!$token = $this->getCubCookie()) {
                if (!$token = $this->request->query($query, false)) {
                    throw new NoJWTOnRequestException();
                }
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
     * Get token from cub cookie
     *
     * @return false|string
     */
    protected function getCubCookie() 
    {
        if (!isset($_COOKIE[self::CUB_COOKIE]) || $_COOKIE[self::CUB_COOKIE] == '') {
            return false;
        }

        return $_COOKIE[self::CUB_COOKIE];
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function createObject(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        if (is_array($fields)) {
            $attributes = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $cubObject->{$cubField};
                    if (in_array($appField, $model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $attributes[$appField] = $value;
                }
            }
            if (count($attributes)) {
                return (bool) $model->create($attributes);
            }
        }

        return false;
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function updateObject(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        $appObject = $this->getObjectById($objectType, $cubObject->id);
        if (is_array($fields)) {
            $updates = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $cubObject->{$cubField};
                    if (in_array($appField, $model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $updates[$appField] = $value;
                }
            }
            if (count($updates)) {
                return $appObject->update($updates);
            }
        }

        return false;
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function deleteObject(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $appObject = $this->getObjectById($objectType, $cubObject->id);
        return $appObject->delete();
    }
}
