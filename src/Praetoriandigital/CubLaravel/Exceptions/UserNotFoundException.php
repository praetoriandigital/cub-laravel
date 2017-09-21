<?php namespace Praetoriandigital\CubLaravel\Exceptions;

class UserNotFoundException extends \Exception
{
    /**
     * UserNotFoundException constructor.
     *
     * @param string|null $cubId
     */
    public function __construct($cubId = null)
    {
        $this->message = 'User not found with cub_id '.($cubId != '' ? : '{empty_string}');
    }
}