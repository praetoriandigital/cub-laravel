<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public $table = 'members';

    public $fillable = [
        'organization',
        'user',
        'invitation',
        'personal_id',
        'post_id',
        'notes',
        'is_active',
        'is_admin',
        'positions',
        'group_membership',
        'created',
    ];
}
