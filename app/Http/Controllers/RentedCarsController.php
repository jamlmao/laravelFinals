<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarsCollection;
use App\Models\RentedCar;
use App\Models\Car;
use App\Http\Resources\RentedCarResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CarResource;

class RentedCarsController extends Controller
{
    public function index(){
        $rentedCars = RentedCar::with(['user', 'car'])->get();

        if (request()->expectsJson()) {
            return RentedCarResource::collection($rentedCars);
        }
    
        return view('rented_cars.index', compact('rentedCars'));
    }


        public function store(Request $request)
        {
            $request->validate([
                'user_id' => 'required',
                'car_id' => 'required'
            ]);
        
            $car = Car::find($request->car_id);
        
            if (!$car) {
                return response()->json(['message' => 'Car not found'], 404);
            }
        
            $activeRental = RentedCar::where('car_id', $request->car_id)
                                    ->where('status', 'rented')
                                    ->first();
        
            if ($activeRental) {
                return response()->json(['message' => 'Car is not available for rent'], 400);
            }
        
            $rentedCar = RentedCar::create([
                'user_id' => $request->user_id,
                'car_id' => $request->car_id,
                'status' => 'rented'  
            ]);
        
            return response()->json([
                'message' => 'Car rented successfully.',
                'rentedCar' => $rentedCar
            ], 200);
        }

        public function userRentedCars($userId)
            {
                $rentedCars = RentedCar::with(['car' => function ($query) {
                    $query->withTrashed();
                }])
                ->where('user_id', $userId)
                ->get();

                $rentedCars = $rentedCars->map(function ($rentedCar) {
                    if ($rentedCar->car) {
                        if ($rentedCar->car->trashed()) {
                            $rentedCar->car_status = 'This car has been deleted';
                        } else {
                            $rentedCar->car_status = 'This car is available';
                        }
                    } else {
                        $rentedCar->car_status = 'Car not found';
                    }
                    return $rentedCar;
                });

                return response()->json(['rentedCars' => $rentedCars]);
            }
    



    public function returnCar(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'car_id' => 'required'
        ]);
    
        $rentedCar = RentedCar::where('user_id', $request->user_id)
                              ->where('car_id', $request->car_id)
                              ->where('status', 'rented')
                              ->first();
    
        if (!$rentedCar) {
            return response()->json(['message' => 'No active rental found for this car and user'], 404);
        }
    
        $rentedCar->status = 'available';
        $rentedCar->save();
    
        return response()->json(['message' => 'Car returned successfully']);
    }

    



}





    // public function show($id)
    // {
    //     $rentedCar = RentedCar::find($id);

    //     if (!$rentedCar) {
    //         return response()->json(['message' => 'Rented car not found'], 404);
    //     }

    //     return new RentedCarResource($rentedCar);
    // }

    // public function destroy($id)
    // {
    //     $rentedCar = RentedCar::find($id);

    //     if (!$rentedCar) {
    //         return response()->json(['message' => 'Rented car not found'], 404);
    //     }

    //     $rentedCar->delete();

    //     return response()->json(['message' => 'Rented car deleted']);
    // }



