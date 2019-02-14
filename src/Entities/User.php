<?php

namespace App\Entities;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'password',
        'is_active'
    ];

    public function client()
    {
        return $this->belongsTo('App\Entities\Client');
    }

    public function comments()
    {
        return $this->hasMany('App\Entities\Comment');
    }

    public function notifications()
    {
        return $this->hasMany('App\Entities\Notification');
    }

    public function projects()
    {
        return $this->hasMany('App\Entities\Project', 'user_id');
    }

    public function projectsAssigned()
    {
        return $this->belongsToMany('App\Entities\Project', 'project_user', 'user_id', 'project_id');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Entities\Role', 'role_user', 'user_id', 'role_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Entities\Tag');
    }

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function setPassword($password)
    {
        $this->update([
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
}
