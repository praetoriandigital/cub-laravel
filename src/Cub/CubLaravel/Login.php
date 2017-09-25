<?php namespace Cub\CubLaravel;

use Illuminate\Database\Eloquent\Model;

class Login
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * @var string
     */
    protected $token;

    /**
     * Login constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param $token
     */
    public function __construct(Model $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
