<?php

namespace App\Entities;

class Expense extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'expense';
    protected $primaryKey = 'expense_id';
    protected $fillable = [
        'description',
    ];

    public function project()
    {
        return $this->belongsTo('App\Entities\Project');
    }
}
