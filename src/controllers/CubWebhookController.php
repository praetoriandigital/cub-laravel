<?php namespace Praetoriandigital\CubLaravel\Controllers;

use Cub_Object;
use Cub_User;
use Praetoriandigital\CubLaravel\Cub;

class CubWebhookController
{
    /**
     * Process webhook
     */
    public function receive()
    {
        // Handle json data
        $object = Cub_Object::fromJson($jsonData);

        if ($object instanceof Cub_User) {
            $user = Cub::getUserByCubId($object->id);
            if ($user) {
                if ($object->deleted) {
                    $user->delete();
                } else {
                    $user->email = $object->email;
                    $user->save();
                }
            }
        }
    }
}
