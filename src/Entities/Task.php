<?php

namespace App\Entities;

class Task extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'task';
    protected $primaryKey = 'task_id';
    protected $dates = [
        'created_at',
        'updated_at',
        'due_at',
        'done_at',
    ];
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'staff_id',
        'project_id',
        'due_at',
    ];

    public function comments()
    {
        return $this->morphMany('App\Entities\Comment', 'commentable');
    }

    public function notifications()
    {
        return $this->morphMany('App\Entities\Notification', 'notificable');
    }

    public function project()
    {
        return $this->belongsTo('App\Entities\Project', 'project_id');
    }

    public function staff()
    {
        return $this->belongsTo('App\Entities\User', 'staff_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Entities\User', 'user_id');
    }

    public function timetracks()
    {
        return $this->hasMany('App\Entities\TimeTrack', 'task_id');
    }
}
