<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RentedCar extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
         'car_id', 
         'status'
        ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
      
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id')->withTrashed();
        
    }

   
   
}
