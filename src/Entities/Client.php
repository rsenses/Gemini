<?php

namespace App\Entities;

class Client extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'client';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasOne('App\Entities\User');
    }

    public function project()
    {
        return $this->hasOne('App\Entities\Project');
    }
}
