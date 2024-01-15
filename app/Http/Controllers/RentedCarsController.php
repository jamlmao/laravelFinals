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

        public function reserveCar(Request $request)
        {
            $request->validate([
                'user_id' => 'required',
                'car_id' => 'required'
            ]);
        
            $car = Car::find($request->car_id);
        
            if (!$car) {
                return response()->json(['message' => 'Car not found'], 404);
            }
        
            if ($car->status != 'available') {
                return response()->json(['message' => 'Car is not available for reservation'], 400);
            }
        
            $car->status = 'reserved'; 
            $car->save();
        
            $rentedCar = RentedCar::create([
                'user_id' => $request->user_id,
                'car_id' => $request->car_id,
                'status' => 'reserved'  
            ]);
        
            return response()->json([
                'message' => 'Car reserved successfully.',
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
                    return response()->json(['message' => 'No rented car found for this user'], 404);
                }
            
                $car = Car::find($rentedCar->car_id);
                $car->status = 'available';
                $car->save();
            
                $rentedCar->status = 'available';
                $rentedCar->save();
            
                return response()->json([
                    'message' => 'Car returned successfully.',
                    'rentedCar' => $rentedCar
                ], 200);
            }
                    

            public function pickupCar(Request $request)
        {
            $request->validate([
                'user_id' => 'required',
                'car_id' => 'required',
                'pickup_date' => 'required|date'
            ]);

            $rentedCar = RentedCar::where('user_id', $request->user_id)
                                ->where('car_id', $request->car_id)
                                ->where('status', 'reserved')
                                ->first();

            if (!$rentedCar) {
                return response()->json(['message' => 'No reserved car found for this user'], 404);
            }

            $car = Car::find($rentedCar->car_id);

            if ($car->status != 'reserved') {
                return response()->json(['message' => 'Car is not reserved'], 400);
            }

            $car->status = 'rented';
            $car->save();

            $rentedCar->status = 'rented';
            $rentedCar->pickup_date = $request->pickup_date;
            $rentedCar->save();

            return response()->json([
                'message' => 'Car picked up successfully.',
                'Rent' => $rentedCar
            ], 200);
        }

            
  

}






    // public function destroy($id)
    // {
    //     $rentedCar = RentedCar::find($id);

    //     if (!$rentedCar) {
    //         return response()->json(['message' => 'Rented car not found'], 404);
    //     }

    //     $rentedCar->delete();

    //     return response()->json(['message' => 'Rented car deleted']);
    // }



