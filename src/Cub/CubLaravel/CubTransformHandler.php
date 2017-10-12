<?php namespace Cub\CubLaravel;

use Cub\CubLaravel\Contracts\CubTransformer;

class CubTransformHandler
{
    /**
     * @var \Cub\CubLaravel\Contracts\CubTransformer
     */
    protected $transformer;

    /**
     * @param \Cub\CubLaravel\Contracts\CubTransformer
     */
    public function __construct(CubTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @return bool
     */
    public function create()
    {
        return $this->transformer->create();
    }

    /**
     * @return bool
     */
    public function update()
    {
        return $this->transformer->update();
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->transformer->delete();
    }
}