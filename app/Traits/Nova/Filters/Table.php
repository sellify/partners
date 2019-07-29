<?php

namespace App\Traits\Nova\Filters;

trait Table
{
    /**
     * Main table
     * @var string
     */
    public $table;

    /**
     * Set table
     * @param $table
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }
}
