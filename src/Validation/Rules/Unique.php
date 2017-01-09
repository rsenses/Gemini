<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Unique extends AbstractRule
{
    protected $table;
    protected $column;

    public function __construct($table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }

    public function validate($input)
    {
        $class = 'App\\Entities\\'.$this->table;
        return $class::where($this->column, $input)->count() === 0;
    }
}
