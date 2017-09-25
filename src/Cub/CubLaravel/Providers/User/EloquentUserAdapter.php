<?php namespace Cub\CubLaravel\Providers\User;

use Illuminate\Database\Eloquent\Model;

class EloquentUserAdapter implements UserInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * Create a new User instance
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     */
    public function __construct(Model $user)
    {
        $this->user = $user;
    }
}
