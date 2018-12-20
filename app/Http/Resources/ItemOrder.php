<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Storage;

class ItemOrder extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'photo_url' => Storage::url("items/$this->photo_url"),
            'photo_filename' => $this->photo_url,
            'price' => $this->price,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order_id' => $this->order_id,
            'order_state' => $this->order_state,
            'order_created_at' => $this->order_created_at,
            'order_updated_at' => $this->order_updated_at,
            'order_start' => $this->order_start,
            'order_end' => $this->order_end
        ];
    }
}
