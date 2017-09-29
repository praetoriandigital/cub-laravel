<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $fillable = [
        'cub_id',
        'first_name',
        'last_name',
        'email',
        'username',
        'last_login',
    ];

    protected $dates = ['last_login'];
}
