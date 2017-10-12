<?php namespace Cub\CubLaravel\Controllers;

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
            $object = Cub_Object::fromJson(json_encode(Input::all()));

            if (in_array(strtolower(get_class($object)), array_keys(Config::get('cub::config.maps')))) {
                if ($object->deleted) {
                    if (Cub::deleteObject($object)) {
                        return $this->respondJSON('deleted', 200);
                    }
                    return $this->respondJSON('error_deleting', 500);
                } else {
                    if (Cub::updateObject($object)) {
                        return $this->respondJSON('updated', 200);    
                    }
                    return $this->respondJSON('error_updating', 500);
                }
            }
            return $this->respondJSON('nothing_to_update_or_create', 200);
        } catch (ObjectNotFoundByCubIdException $e) {
            if (Cub::createObject($object)) {
                return $this->respondJSON('created', 200);
            }
            return $this->respondJSON('error_creating', 500);
        } catch (Exception $e) {
            return $this->respondJSON('internal_error', 500);
        }
    }
}
