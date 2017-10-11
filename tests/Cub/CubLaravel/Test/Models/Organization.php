<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    public $fillable = [
        'name',
        'employees',
        'tags',
        'country',
        'state',
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
