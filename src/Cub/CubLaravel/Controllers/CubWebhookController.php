<?php namespace Cub\CubLaravel\Controllers;

use Config;
use Cub;
use Cub\CubLaravel\Exceptions\UserNotFoundByCubIdException;
use Cub\CubLaravel\Traits\RespondTrait;
use Cub_Object;
use Cub_User;
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
        $object = Cub_Object::fromJson($HTTP_RAW_POST_DATA);

        if ($object instanceof Cub_User) {
            try {
                $user = Cub::getUserById($object->id);
                if ($object->deleted) {
                    if (Cub::deleteUser($user)) {
                        return $this->respondJSON('user_deleted', 200);    
                    }
                    return $this->respondJSON('error_deleting_user', 500);
                } else {
                    if (Cub::updateUser($user, $object)) {
                        return $this->respondJSON('user_updated', 200);    
                    }
                    return $this->respondJSON('error_updating_user', 500);
                }
            } catch (UserNotFoundByCubIdException $e) {
                if (Cub::createUser($object)) {
                    return $this->respondJSON('user_created', 200);
                }
                return $this->respondJSON('error_creating_user', 500);
            } catch (Exception $e) {
                return $this->respondJSON('internal_error', 500);
            }
        }

        return $this->respondJSON('nothing_to_update_or_create', 200);
    }
}
