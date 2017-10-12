<?php namespace Cub\CubLaravel\Filters;

use Cub\CubLaravel\Cub;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Traits\RespondTrait;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Response;

class CubAuthFilter
{
    use RespondTrait;

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
            return $this->respondJSON('token_not_provided', 400);
        }

        try {
            $this->cub->getUserByJWT($token);
        } catch (ObjectNotFoundByCubIdException $e) {
            return $this->respondJSON('user_not_found', 404);
        } catch (ExpiredException $e) {
            return $this->respondJSON('expired_token', 401);
        } catch (BeforeValidException $e) {
            return $this->respondJSON('token_not_yet_valid', 401);
        } catch (\Exception $e) {
            return $this->respondJSON('error_processing_token', 500);
        }
    }
}
