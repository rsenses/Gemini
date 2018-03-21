<?php

namespace App\Entities;

class Tag extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'tag';
    protected $primaryKey = 'tag_id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function projects()
    {
        return $this->belongsToMany('App\Entities\Project');
    }

    public function users()
    {
        return $this->belongsToMany('App\Entities\User');
    }
}
