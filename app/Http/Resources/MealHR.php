<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MealHR extends Resource
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
            'table_number' => $this->table_number,
            'start' => $this->start,
            'end' => $this->end,
            'responsible_waiter_id' => $this->responsible_waiter_id,
            'responsible_waiter_name' => $this->responsible_waiter_name,
            'total_price_preview' => $this->total_price_preview,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
