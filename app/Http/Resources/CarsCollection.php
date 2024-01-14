<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'desc' => $this->desc,
            'name' => $this->name,
            'brand' => $this->brand,
            'price' => $this->price,
            'image' => $this->image,
        ];
    }
}