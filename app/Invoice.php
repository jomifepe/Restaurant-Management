<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'state', 'meal_id', 'nif', 'name', 'date','total_price'
    ];


    public function meal()
    {
        return $this->hasMany('App\Meal');
    }


}
