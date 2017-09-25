<?php namespace Cub\CubLaravel;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\UserNotFoundByCubIdException;
use Response;

class CubAuthFilter
{
    /**
     * @param Cub
     */
    protected $cub;

    /**
     * CubAuthFilter constructor.
     *
     * @param Cub $cub
     */
    public function __construct(Cub $cub)
    {
        $this->cub = $cub;
    }

    /**
     * Filter the request
     *
     * @return \Illuminate\Http\Response
     */
    public function filter()
    {
        try {
            $token = $this->cub->getRequestJWT();
        } catch (NoJWTOnRequestException $e) {
            return $this->respond('token_not_provided', 400);
        }

        try {
            $this->cub->getUserByJWT($token);
        } catch (UserNotFoundByCubIdException $e) {
            return $this->respond('user_not_found', 404);
        } catch (ExpiredException $e) {
            return $this->respond('expired_token', 401);
        } catch (BeforeValidException $e) {
            return $this->respond('token_not_yet_valid', 401);
        } catch (\Exception $e) {
            return $this->respond('error_processing_token', 500);
        }
    }

    /**
     * Fire event and return the response
     *
     * @param  string   $error
     * @param  integer  $status
     * @return mixed
     */
    protected function respond($error, $status)
    {
        return Response::json(['error' => $error], $status);
    }
}
