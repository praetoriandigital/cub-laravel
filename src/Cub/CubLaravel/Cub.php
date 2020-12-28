<?php namespace Cub\CubLaravel;

use Config;
use Cub\CubLaravel\Contracts\CubGateway;
use Cub\CubLaravel\Contracts\CubLogin;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Handlers\Transformers\CubTransformHandler;
use Cub\CubLaravel\Support\Login;
use Cub\CubLaravel\Transformers\CubObjectTransformer;
use Cub_Api;
use Cub_ApiError;
use Cub_NotFound;
use Cub_Object;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;

class Cub
{
    const ALGO = 'HS256';
    const CUB_ID_KEY = 'user';
    const CUB_USER_NAME = 'user';
    const CUB_ORG_NAME = 'organization';
    const CUB_USER_COOKIE = 'cubUserToken';
    const CUB_ORG_COOKIE = 'cubOrganizationId';

    /** @var CubGateway */
    private $cubGateway;

    /** @var CubLogin */
    private $loginService;

    /** @var Request */
    protected $request;

    private $currentUser;

    private $currentToken;

    private $currentOrganizationId;

    /**
     * Cub constructor.
     *
     * @param CubGateway $cubGateway
     * @param CubLogin $loginService
     * @param Request $request
     */
    public function __construct(CubGateway $cubGateway, CubLogin $loginService, Request $request)
    {
        $this->cubGateway = $cubGateway;
        $this->loginService = $loginService;
        $this->request = $request;
    }

    /**
     * @param $username
     * @param $password
     * @param bool $setCookie
     *
     * @return Login
     */
    public function login($username, $password, $setCookie = false)
    {
        $cub_user = $this->loginService->login($username, $password);
        $user = $this->getUserById($cub_user->id);

        $this->setCurrentUser($user);
        $this->setCurrentToken($cub_user->token);
        
        if ($setCookie) {
            $this->setCubUserCookie($cub_user->token);
        }

        return new Login($user, $cub_user->token);
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->setCurrent();
        $this->clearCookies();
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
     * @param string|null
     *
     * @return void
     */
    public function setCurrentOrganizationId($cubOrgId = null)
    {
        if ($cubOrgId && substr($cubOrgId, 0, 4) === 'org_') {
            $this->currentOrganizationId = $cubOrgId;
            $this->setCubOrganizationIdCookie($cubOrgId);
        } else {
            $this->currentOrganizationId = null;
        }

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
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function currentOrganization()
    {
        if (!$orgCubId = $this->currentOrganizationId) {
            if (!$orgCubId = $this->getCubOrgCookie()) {
                return null;
            }
        }

        return $this->getOrganizationById($orgCubId);
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
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOrganizationById($cubId)
    {
        return $this->getObjectById(self::CUB_ORG_NAME, $cubId);
    }

    /**
     * @param $cubId
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getNewObject($objectType)
    {
        if ($modelName = config('cub.maps.'.$objectType.'.model')) {
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
        $model = app()->make(config('cub.maps.'.$objectType.'.model'));
        if (method_exists($model, 'withTrashed')) {
            $object = app()->make(config('cub.maps.'.$objectType.'.model'))->withTrashed()->whereCubId($cubId)->first();
        } else {
            $object = app()->make(config('cub.maps.'.$objectType.'.model'))->whereCubId($cubId)->first();
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
     * @throws NoJWTOnRequestException
     */
    public function getUserByJWT($token = null)
    {
        if (!$token) {
            $token = $this->getRequestJWT();
        }

        $decoded = (array) JWT::decode($token, config('cub.secret_key'), [self::ALGO]);

        if (isset($decoded['scope'])) {
            throw new InvalidArgumentException('JWT scope claim can not be present.');
        }

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
            $decoded = (array) JWT::decode($jwt, config('cub.secret_key'), [self::ALGO]);

            if (isset($decoded['scope'])) {
                throw new InvalidArgumentException('JWT scope claim can not be present.');
            }
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
            if (!$token = $this->getCubUserCookie()) {
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

        if (!starts_with(strtolower($header), $method)) {
            return false;
        }

        return trim(str_ireplace($method, '', $header));
    }

    /**
     * Get token from cub cookie
     *
     * @return false|string
     */
    protected function getCubUserCookie() 
    {
        if (!isset($_COOKIE[self::CUB_USER_COOKIE]) || $_COOKIE[self::CUB_USER_COOKIE] == '') {
            return false;
        }

        return $_COOKIE[self::CUB_USER_COOKIE];
    }

    /**
     * Get token from cub cookie
     *
     * @return false|string
     */
    protected function getCubOrgCookie() 
    {
        if (!isset($_COOKIE[self::CUB_ORG_COOKIE]) || $_COOKIE[self::CUB_ORG_COOKIE] == '') {
            return false;
        }

        return $_COOKIE[self::CUB_ORG_COOKIE];
    }

    /**
     * Set the Cub User cookie
     *
     * @param string $token
     *
     * @return bool
     */
    private function setCubUserCookie($token)
    {
        JWT::decode($token, config('cub.secret_key'), [self::ALGO]);

        if (!headers_sent() && (!isset($_COOKIE[self::CUB_USER_COOKIE]) || $_COOKIE[self::CUB_USER_COOKIE] != $token)) {
            unset($_COOKIE[self::CUB_USER_COOKIE]);
            setcookie(self::CUB_USER_COOKIE, $token, 0, '/');
            return true;
        }

        return false;
    }

    /**
     * Set the Cub Organization cookie
     *
     * @param string $cubOrgId
     *
     * @return bool
     */
    private function setCubOrganizationIdCookie($cubOrgId)
    {
        if (!headers_sent() && substr($cubOrgId, 0, 4) === 'org_' && (!isset($_COOKIE[self::CUB_ORG_COOKIE]) || $_COOKIE[self::CUB_ORG_COOKIE] != $cubOrgId)) {
            unset($_COOKIE[self::CUB_ORG_COOKIE]);
            setcookie(self::CUB_ORG_COOKIE, $cubOrgId, 0, '/');
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
        if (!headers_sent()) {
            $this->clearUserCookie();
            $this->clearOrgCookie();
        }
    }

    /**
     * Clear Cub cookies
     *
     * @return void
     */
    public function clearUserCookie()
    {
        unset($_COOKIE[self::CUB_USER_COOKIE]);
        setcookie(self::CUB_USER_COOKIE, null, -1, '/');
    }

    /**
     * Clear Cub cookies
     *
     * @return void
     */
    public function clearOrgCookie()
    {
        unset($_COOKIE[self::CUB_ORG_COOKIE]);
        setcookie(self::CUB_ORG_COOKIE, null, -1, '/');
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
        return $this->objectNameIsTracked($this->objectType($cubObject));
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function objectNameIsTracked($cubObjectName)
    {
        return in_array($cubObjectName, array_keys(config('cub.maps')));
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return string|null
     */
    public function getObjectExpands(Cub_Object $cubObject)
    {
        if ($this->objectIsTracked($cubObject)) {
            $relations = config('cub.maps.'.$this->objectType($cubObject).'.relations');
            if ($relations && is_array($relations)) {
                $expands = '';
                $roots = array_keys($relations);
                $expands .= implode(',', $roots);
                foreach ($roots as $root) {
                    $relations = config('cub.maps.'.$root.'.relations');
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
    public function processObject(Cub_Object $originalCubObject, $reload = true)
    {
        if ($reload) {
            try {
                $cubObject = $this->cubGateway->reload($originalCubObject, ['expand' => Cub::getObjectExpands($originalCubObject)]);
            } catch (Cub_NotFound $e) {
                try {
                    $objectType = $this->objectType($originalCubObject);
                    $object = $this->getObjectById($objectType, $originalCubObject->id);
                    return !isset($object->deleted_at) ? $object->delete() : true;
                } catch (ObjectNotFoundByCubIdException $e) {
                    return true;
                }
            }
        } else {
            $cubObject = $originalCubObject;
        }
        $objectType = $this->objectType($cubObject);
        if (config('cub.maps.'.$objectType.'.transformer')) {
            $transformer = app()->makeWith(config('cub.maps.'.$objectType.'.transformer'), ['cubObject' => $cubObject]);
        } else {
            $transformer = new CubObjectTransformer($cubObject);
        }

        $handler = new CubTransformHandler($transformer);
        return $handler->handle();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     * @param array $params
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function get(Model $appObject, array $params = [])
    {
        $cubObject = $this->convertAppObject($appObject);
        $response = Cub_Object::execGet(get_class($cubObject), null, $params);

        if (is_array($response) && count($response) == 1) {
            return $this->processObject(array_shift($response));
        }

        return null;
    }
    
    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     * @param array $params
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function create(Model $appObject, array $params = [])
    {
        $cubObject = $this->convertAppObject($appObject);

        return $this->processObject(Cub_Object::execCreate(get_class($cubObject), $params));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     * @param array $params
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function update(Model $appObject, array $params = [])
    {
        $cubObject = $this->convertAppObject($appObject);
        $instance = new $cubObject($params);

        return $this->processObject($instance->execSave());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     *
     * @return Cub_Object
     */
    private function convertAppObject(Model $appObject)
    {
        $maps = config('cub.maps');
        $objectName = '';
        $appObjectName = get_class($appObject);
        foreach ($maps as $k => $map) {
            if ($map['model'] == $appObjectName) {
                $objectName = $k;
                break;
            }
        }
        return Cub_Object::fromArray(['object' => $objectName]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     * @param array $params
     *
     * @return bool
     */
    public function updateMemberPermissions(Model $appObject, array $params = [])
    {
        $memberClassName = config('cub.maps.member.model');
        $memberClass = new $memberClassName;
        if ($appObject instanceof $memberClass) {
            $result = Cub_Api::post('members/'.$appObject->cub_id.'/permissions', $params);
            $comparisonArray = array_intersect_key($result, $params);
            if ($comparisonArray == $params) {
                return $this->refreshObject($appObject);
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     *
     * @return bool
     */
    public function removeMember(Model $appObject)
    {
        $memberClassName = config('cub.maps.member.model');
        $memberClass = new $memberClassName;
        if ($appObject instanceof $memberClass) {
            $cubObject = $this->convertAppObject($appObject);
            $instance = new $cubObject(['id' => $appObject->cub_id]);
            try {
                return $instance->execRemove();
            } catch (Cub_ApiError $e) {
                if ($e->getHttpCode() == 200) {
                    $this->refreshObject($appObject);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function refreshObject(Model $appObject)
    {
        $cubObject = $this->convertAppObject($appObject);
        $instance = new $cubObject(['id' => $appObject->cub_id]);
        return $this->processObject($instance);
    }
}
