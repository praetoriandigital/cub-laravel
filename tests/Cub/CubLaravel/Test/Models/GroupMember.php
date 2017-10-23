<?php namespace Cub\CubLaravel\Test\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $table = 'groups_members';

    public $fillable = [
        'cub_id',
        'group',
        'member',
        'is_admin',
        'created',
    ];
}
