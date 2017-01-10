<?php

namespace App\Entities;

class Project extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'project';
    protected $primaryKey = 'project_id';
    protected $dates = [
        'created_at',
        'updated_at',
        'started_at',
        'due_at',
        'done_at',
        'billed_at',
    ];
    protected $fillable = [
        'name',
        'description',
        'short_description',
        'contact',
        'budget',
        'bill',
        'color',
        'user_id',
        'client_id',
        'started_at',
        'due_at',
        'billed_at',
    ];

    public function client()
    {
        return $this->belongsTo('App\Entities\Client');
    }

    public function comments()
    {
        return $this->morphMany('App\Entities\Comment', 'commentable');
    }

    public function expenses()
    {
        return $this->hasMany('App\Entities\Expense');
    }

    public function tasks()
    {
        return $this->hasMany('App\Entities\Task');
    }

    public function user()
    {
        return $this->belongsTo('App\Entities\User');
    }

    public function users()
    {
        return $this->belongsToMany('App\Entities\User');
    }
}
