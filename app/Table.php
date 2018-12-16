<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    protected $table = 'restaurant_tables';
    public $primaryKey = 'table_number';
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'created_at', 'updated_at'
    ];
}
