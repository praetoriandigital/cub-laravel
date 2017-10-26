<?php namespace Cub\CubLaravel\Http\Controllers;

use Config;
use Cub;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub\CubLaravel\Traits\RespondTrait;
use Cub_Object;
use Illuminate\Routing\Controller;
use Input;

class CubWebhookController extends Controller
{
    use RespondTrait;

    /**
     * Process Cub Webhook data
     */
    public function receive()
    {
        try {
            $object = Cub_Object::fromArray(Input::all());
            if ($object instanceof Cub_Object && Cub::objectIsTracked($object)) {
                if (Cub::processObject($object)) {
                    return $this->respondJSON('processed', 200);
                }
                return $this->respondJSON('error_processing', 500);
            }
            return $this->respondJSON('nothing_to_process', 200);
        } catch (Exception $e) {
            return $this->respondJSON('internal_error', 500);
        }
    }
}
