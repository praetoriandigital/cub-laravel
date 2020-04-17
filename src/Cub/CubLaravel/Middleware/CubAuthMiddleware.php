<?php namespace Cub\CubLaravel\Middleware;

use Closure;
use Cub\CubLaravel\Cub;
use Cub\CubLaravel\Exceptions\NoJWTOnRequestException;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Traits\RespondTrait;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use InvalidArgumentException;

class CubAuthMiddleware
{
    use RespondTrait;

    /**
     * @param Cub
     */
    protected $cub;

    /**
     * CubAuthMiddleware constructor.
     *
     * @param Cub $cub
     */
    public function __construct(Cub $cub)
    {
        $this->cub = $cub;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
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
        } catch (InvalidArgumentException $e) {
            return $this->respondJSON('invalid_token', 401);
        } catch (ExpiredException $e) {
            return $this->respondJSON('expired_token', 401);
        } catch (BeforeValidException $e) {
            return $this->respondJSON('token_not_yet_valid', 401);
        } catch (\Exception $e) {
            return $this->respondJSON('error_processing_token', 500);
        }

        return $next($request);
    }
}
