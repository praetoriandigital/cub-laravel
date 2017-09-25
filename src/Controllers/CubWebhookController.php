<?php namespace Praetoriandigital\CubLaravel\Controllers;

use Config;
use Cub;
use Cub_Object;
use Cub_User;
use Illuminate\Routing\Controller;
use Input;

class CubWebhookController extends Controller
{
    /**
     * Process Cub Webhook data
     */
    public function receive()
    {
        // Handle json data
        // TODO: Update with real payload key
        $jsonData = Input::get('payload', json_encode([]));
        $object = Cub_Object::fromJson($jsonData);

        if ($object instanceof Cub_User) {
            $user = Cub::getUserById($object->id);
            if ($user) {
                if ($object->deleted) {
                    Cub::deleteUser($user);
                } else {
                    Cub::updateUser($user, $object);
                }
            }
        }
    }
}
