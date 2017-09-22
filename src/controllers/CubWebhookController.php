<?php namespace Praetoriandigital\CubLaravel\Controllers;

use Config;
use Cub_Object;
use Cub_User;
use Cub;
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
            $user = Cub::getUserByCubId($object->id);
            if ($user) {
                if ($object->deleted) {
                    $user->delete();
                } else {
                    $fields = Config::get('cub.fields');
                    $updates = [];
                    foreach ($fields as $cubField => $appField) {
                        $updates[$appField] = $object->{$cubField};
                    }
                    if (count($updates)) {
                        $user->update($updates);
                    }
                }
            }
        }
    }
}
