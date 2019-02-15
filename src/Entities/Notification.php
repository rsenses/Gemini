<?php

namespace App\Entities;

class Notification extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'notification';
    protected $primaryKey = 'notification_id';
    protected $dates = [
        'created_at',
        'updated_at',
        'showed_at',
    ];
    protected $fillable = [
        'user_id',
        'description',
        'is_readed',
        'showed_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\Entities\User', 'user_id');
    }

    public function notificable()
    {
        return $this->morphTo();
    }
}
