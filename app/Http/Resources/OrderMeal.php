<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Storage;

class OrderMeal extends Resource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return 
        [
            'id' => $this->id,
            'state' => $this->state,
            'item_id' => $this->item_id,
            'meal_id' => $this->meal_id,
            'meal_table_number' => $this->meal_table_number,
            'responsible_cook_id' => $this->responsible_cook_id,
            'item_name' => $this->item_name,
            'item_type' => $this->item_type,
            'item_photo' => $this->item_photo,  
            'item_photo_src' => Storage::url("items/$this->item_photo"),
            'start' => $this->start,
            'end' => $this->end,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
