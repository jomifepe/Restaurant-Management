<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'state', 'item_id', 'meal_id', 'responsible_cook_id','start', 'end'
    ];


    public function meal()
    {
        return $this->belongsTo('App\Meal');
    }

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }



}
