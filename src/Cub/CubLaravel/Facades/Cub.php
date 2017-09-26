<?php namespace Cub\CubLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Cub extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cub';
    }
}
