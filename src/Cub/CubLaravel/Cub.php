<?php namespace Cub\CubLaravel;

use Carbon\Carbon;
use Config;
use Cub\CubLaravel\Contracts\CubGateway;
use Cub\CubLaravel\Contracts\CubTransformer;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Handlers\Transformers\CubTransformHandler;
use Cub\CubLaravel\Support\Login;
use Cub\CubLaravel\Transformers\CubObjectTransformer;
use Cub_Object;
use Cub_User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Cub
{
    const ALGO = 'HS256';
    const CUB_ID_KEY = 'user';
    const CUB_USER_NAME = 'user';
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
     * @param \Cub\CubLaravel\Contracts\CubGateway $cubGateway
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(CubGateway $cubGateway, Request $request)
    {
        $this->cubGateway = $cubGateway;
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
        return $this->getObjectById(self::CUB_USER_NAME, $cubId);
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getNewObject($objectType)
    {
        if ($modelName = Config::get('cub::config.maps.'.$objectType.'.model')) {
            return app()->make($modelName);
        }
        return null;
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws ObjectNotFoundByCubIdException
     */
    public function getObjectById($objectType, $cubId)
    {
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        if (method_exists($model, 'withTrashed')) {
            $object = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'))->withTrashed()->whereCubId($cubId)->first();    
        } else {
            $object = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'))->whereCubId($cubId)->first();
        }
        
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

        $this->setCurrent($this->getUserById($decoded[self::CUB_ID_KEY]), $token);

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
     * Set the Cub Organization cookie
     *
     * @param string $cubOrgId
     *
     * @return bool
     */
    public function setCubOrganizationIdCookie($cubOrgId)
    {
        if (substr($cubOrgId, 0, 4) === 'org_') {
            unset($_COOKIE['cubOrganizationId']);
            setcookie('cubOrganizationId', $cubOrgId, 0, '/');
            return true;
        }

        return false;
    }

    /**
     * Clear Cub cookies
     *
     * @return void
     */
    public function clearCookies()
    {
        unset($_COOKIE['cubUserToken']);
        setcookie('cubUserToken', null, -1, '/');
        unset($_COOKIE['cubOrganizationId']);
        setcookie('cubOrganizationId', null, -1, '/');
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function objectType(Cub_Object $cubObject)
    {
        return str_replace('cub_', '', strtolower(get_class($cubObject)));
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function objectIsTracked(Cub_Object $cubObject)
    {
        return in_array($this->objectType($cubObject), array_keys(Config::get('cub::config.maps')));
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return string|null
     */
    public function getObjectExpands(Cub_Object $cubObject)
    {
        if ($this->objectIsTracked($cubObject)) {
            $relations = Config::get('cub::config.maps.'.$this->objectType($cubObject).'.relations');
            if ($relations && is_array($relations)) {
                $expands = '';
                $roots = array_keys($relations);
                $expands .= implode(',', $roots);
                foreach ($roots as $root) {
                    $relations = Config::get('cub::config.maps.'.$root.'.relations');
                    if ($relations && is_array($relations)) {
                        $relations = array_keys($relations);
                        foreach($relations as $relation) {
                            $expands .= ','.$root.'__'.$relation;
                        }
                    }
                }
                return $expands;
            }
        }
        return null;
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function processObject(Cub_Object $cubObject, $reload = true)
    {
        if ($reload) {
            $cubObject = $this->cubGateway->reload($cubObject, ['expand' => Cub::getObjectExpands($cubObject)]);
        }
        $objectType = $this->objectType($cubObject);
        if (Config::get('cub::config.maps.'.$objectType.'.transformer')) {
            $transformer = app()->make(Config::get('cub::config.maps.'.$objectType.'.transformer'), [$cubObject]);
        } else {
            $transformer = new CubObjectTransformer($cubObject);
        }

        $handler = new CubTransformHandler($transformer);
        return $handler->handle();
    }
}
