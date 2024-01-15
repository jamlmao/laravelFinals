<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'desc',
        'name',
        'brand',
        'price',
        'image',
        'status'
    ];
    protected $dates = ['deleted_at'];
}