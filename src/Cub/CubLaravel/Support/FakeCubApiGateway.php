<?php namespace Cub\CubLaravel\Support;

use Cub_Forbidden;
use Cub_Member;
use Cub_NotFound;
use Cub_Object;
use Cub_User;
use Cub\CubLaravel\Contracts\CubGateway;

class FakeCubApiGateway implements CubGateway
{
    /**
     * @param Cub_Object $cubObject
     * @param array $params
     *
     * @return Cub_Object
     */
    public function reload(Cub_Object $cubObject, array $params = [])
    {
        if ($cubObject->deleted) {
            throw new Cub_NotFound;
        }

        if ($cubObject->forbidden) {
            throw new Cub_Forbidden;
        }

        if ($cubObject->last_login) {
            $cubObject->last_login = new \DateTime($cubObject->last_login, new \DateTimeZone('UTC'));
        } else if ($cubObject->user instanceof Cub_User) {
            $cubObject->user->last_login = new \DateTime($cubObject->user->last_login, new \DateTimeZone('UTC'));
        }

        return $cubObject;
    }
}
