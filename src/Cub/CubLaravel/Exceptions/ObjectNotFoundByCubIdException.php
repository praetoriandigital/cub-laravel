<?php namespace Cub\CubLaravel\Exceptions;

class ObjectNotFoundByCubIdException extends \Exception
{
    /**
     * ObjectNotFoundByCubIdException constructor.
     *
     * @param string|null $cubId
     */
    public function __construct($cubId = null)
    {
        $this->message = 'Object not found with Cub id '.($cubId != '' ? : '{empty_string}');
    }
}
