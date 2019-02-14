<?php

namespace App\Entities;

class TimeTrack extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'time_track';
    protected $primaryKey = 'time_track_id';
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'task_id',
        'is_completed',
        'created_at',
        'updated_at',
    ];

    public function task()
    {
        return $this->belongsTo('App\Entities\Task', 'task_id');
    }
}
