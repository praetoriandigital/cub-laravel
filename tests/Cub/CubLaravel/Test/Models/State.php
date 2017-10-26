<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'states';

    public $fillable = [
        'cub_id',
        'name',
        'code',
        'country',
        'country_id',
        'created',
    ];
}
