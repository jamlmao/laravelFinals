<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserModel;
use App\Http\Resources\RentedCarResource;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the user's rented cars.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function userRentedCars($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $rentedCars = $user->rentedCars;

        return RentedCarResource::collection($rentedCars);
    }
}