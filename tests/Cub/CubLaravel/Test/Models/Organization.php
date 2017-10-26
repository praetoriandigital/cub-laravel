<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    public $fillable = [
        'cub_id',
        'name',
        'employees',
        'tags',
        'country',
        'country_id',
        'state',
        'state_id',
        'city',
        'county',
        'postal_code',
        'address',
        'phone',
        'hr_phone',
        'fax',
        'website',
        'created',
        'logo',
    ];
}
