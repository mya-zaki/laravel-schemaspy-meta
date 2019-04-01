<?php
namespace MyaZaki\LaravelSchemaspyMeta\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The users that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'r_id', 'p_id');
    }
}