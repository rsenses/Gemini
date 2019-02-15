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
        return $this->belongsToMany('App\Entities\Project', 'project_tag', 'tag_id', 'project_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Entities\User', 'tag_user', 'tag_id', 'user_id');
    }
}
