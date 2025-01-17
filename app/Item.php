<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{

    protected $table = 'items';
    public $primaryKey = 'id';
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'description', 'price'
    ];


    public function orders()
    {
        return $this->hasMany('App\Order');
    }





}
