<?php namespace Praetoriandigital\CubLaravel\Exceptions;

class UserNotFoundByCubIdException extends \Exception
{
    /**
     * UserNotFoundByCubIdException constructor.
     *
     * @param string|null $cubId
     */
    public function __construct($cubId = null)
    {
        $this->message = 'User not found with Cub user id '.($cubId != '' ? : '{empty_string}');
    }
}