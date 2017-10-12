<?php namespace Cub\CubLaravel\Contracts;

use Cub_Object;

interface CubTransformer
{
    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function create(Cub_Object $cubObject);

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function update(Cub_Object $cubObject);

    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function delete(Cub_Object $cubObject);
}
