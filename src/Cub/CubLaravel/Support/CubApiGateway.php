<?php namespace Cub\CubLaravel\Support;

use Cub_Object;
use Cub\CubLaravel\Contracts\CubGateway;

class CubApiGateway implements CubGateway
{
    /**
     * @param Cub_Object $cubObject
     * @param array $params
     *
     * @return Cub_Object
     */
    public function reload(Cub_Object $cubObject, array $params = [])
    {
        return $cubObject->execReload($params);
    }
}
