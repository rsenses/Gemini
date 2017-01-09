<?php

namespace App\Entities;

class TimeTrack extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'time_track';
    protected $primaryKey = 'time_track_id';

    public function task()
    {
        return $this->belongsTo('App\Entities\Task');
    }
}
