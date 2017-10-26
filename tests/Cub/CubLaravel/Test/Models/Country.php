<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    public $fillable = [
        'cub_id',
        'name',
        'code',
        'code2',
        'code3',
        'created',
    ];
}
