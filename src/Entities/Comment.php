<?php

namespace App\Entities;

class Comment extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'comment';
    protected $primaryKey = 'comment_id';
    protected $fillable = [
        'description',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }
}
