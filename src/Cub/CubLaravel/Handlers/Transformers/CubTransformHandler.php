<?php namespace Cub\CubLaravel\Handlers\Transformers;

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
    public function handle()
    {
        return $this->transformer->process();
    }
}
