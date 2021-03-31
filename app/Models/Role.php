<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function permissions()
    {
        return $this->belongsTo('App\Permission');
    }

    public function hasPermissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'permission_has_role', 'permission_id', 'role_id');
    }
}
