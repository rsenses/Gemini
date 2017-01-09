<?php

namespace App\Entities;

class Task extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'client';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];

    public function comments()
    {
        return $this->morphMany('App\Entities\Comment', 'commentable');
    }

    public function project()
    {
        return $this->belongsTo('App\Entities\Project');
    }

    public function staff()
    {
        return $this->belongsTo('App\Entities\User', 'staff_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Entities\User');
    }

    public function timetracks()
    {
        return $this->hasMany('App\Entities\TimeTrack');
    }
}
