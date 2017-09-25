<?php namespace Cub\CubLaravel\Exceptions;

class NoJWTOnRequestException extends \Exception
{
    /**
     * NoJWTOnRequestException constructor.
     *
     * @param string|null $cubId
     */
    public function __construct($cubId = null)
    {
        $this->message = 'No JWT exists on the request.';
    }
}
