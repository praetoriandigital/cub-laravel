<?php namespace Cub\CubLaravel\Traits;

use Response;

trait RespondTrait
{
    /**
     * Return a json response
     *
     * @param  string   $message
     * @param  integer  $status
     * @return mixed
     */
    protected function respondJSON($message, $status)
    {
        return Response::json([($status == 200 ? 'message' : 'error') => $message], $status);
    }
}
