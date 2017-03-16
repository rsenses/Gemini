<?php

namespace App\Entities;

class Comment extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'comment';
    protected $primaryKey = 'comment_id';
    protected $fillable = [
        'description',
        'user_id',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('App\Entities\User');
    }
}
