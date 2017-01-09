<?php

namespace App\Entities;

class Role extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'role';
    protected $primaryKey = 'role_id';
    protected $fillable = [
        'name',
        'permissions',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Entities\User');
    }
}
