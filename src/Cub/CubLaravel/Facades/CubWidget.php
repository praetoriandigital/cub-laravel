<?php  namespace Cub\CubLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class CubWidget extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cub-widget';
    }
}
