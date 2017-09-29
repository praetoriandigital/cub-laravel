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
        if ($status === 200 || $status === 201) {
            $key = 'message';
        } else {
            $key = 'error';
        }

        return Response::json([$key => $message], $status);
    }
}
