<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class InvoiceDetailed extends Resource
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
            'meal_id' => $this->meal_id,
            'responsible_waiter_id' => $this->responsible_waiter_id,
            'table_number' => $this->table_number,
            'responsible_waiter_name' => $this->responsible_waiter_name,
            'nif' => $this->nif,
            'name' => $this->name,
            'date' => $this->date,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}