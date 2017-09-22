<?php namespace Praetoriandigital\CubLaravel\Controllers;

use Cub_Object;
use Cub_User;
use Cub;

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
                    $fields = Config::get('cub.fields');
                    $updates = [];
                    foreach ($fields as $cub => $local) {
                        $updates[$local] = $user->get($cub);
                    }
                    if (count($updates)) {
                        $user->update($updates);
                    }
                }
            }
        }
    }
}
