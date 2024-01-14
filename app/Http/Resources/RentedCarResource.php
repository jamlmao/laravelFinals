<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentedCarResource extends JsonResource
{
    
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'user' =>$this->user,
            'car' => $this->car,
            
        ];
    }
}
