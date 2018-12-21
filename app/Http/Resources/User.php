<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Storage;

class User extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'type' => $this->type,
            'photo_url' => Storage::url("profiles/$this->photo_url"),
            'photo_filename' => $this->photo_url,
            'shift_active' => $this->shift_active,
            'last_shift_start' => $this->last_shift_start,
            'last_shift_end' => $this->last_shift_end,
            'blocked' => $this->blocked,
            'deleted_at' => $this->deleted_at,
            'blockedStr' => $this->blocked ? 'Yes' : 'No',
        ];
    }




}
