<?php namespace Cub\CubLaravel\Contracts;

use Cub_Object;

interface CubTransformer
{
    /**
     * @param Cub_Object $cubObject
     */
    public function __construct(Cub_Object $cubObject);

    /**
     * @return bool
     */
    public function process();
}
