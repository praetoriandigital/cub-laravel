<?php namespace Cub\CubLaravel\Contracts;

use Cub_Object;

interface CubGateway
{
    /**
     * @param Cub_Object $cubObject
     * @param array $params
     *
     * @return Cub_Object
     */
    public function reload(Cub_Object $cubObject, array $params = []);
}
