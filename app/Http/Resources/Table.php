<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Table extends Resource
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
                'table_number' => $this->table_number,
                'deleted_at' => $this->deleted_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
    }
}
