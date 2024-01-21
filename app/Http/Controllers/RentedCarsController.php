<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CarsCollection;
use App\Models\RentedCar;
use App\Models\Car;
use App\Http\Resources\RentedCarResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CarResource;

class RentedCarsController extends Controller
{
    //get all rented cars
    public function index(){
        $rentedCars = RentedCar::with(['user', 'car'])->get();

        if (request()->expectsJson()) {
            return RentedCarResource::collection($rentedCars);
        }
    
        return view('rented_cars.index', compact('rentedCars'));
    }

    //reserveCar
    public function reserveCar($carId)
    {
        $userId = Auth::id();
    
        $car = Car::find($carId);
    
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    
        if ($car->status != 'available') {
            return response()->json(['message' => 'Car is not available for reservation'], 400);
        }
    
        $car->status = 'reserved'; 
        $car->save();
    
        $rentedCar = RentedCar::create([
            'user_id' => $userId,
            'car_id' => $carId,
            'status' => 'reserved'  
        ]);
    
        return response()->json([
            'message' => 'Car reserved successfully.',
            'rentedCar' => $rentedCar
        ], 200);
    }
    
       

        //userRentedCars
        public function userRentedCars($userId)
            {
                $rentedCars = RentedCar::with(['car' => function ($query) {
                    $query->withTrashed();
                }])
                ->where('user_id', $userId)
                ->where('status', 'reserved') 
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
    

            //returnCar

            public function returnCar(Request $request)
            {
                $request->validate([
                   
                    'car_id' => 'required'
                ]);
                $userId = auth()->id();
                $rentedCar = RentedCar::where('user_id', $userId)
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

                //pickupCar
                public function pickupCar(Request $request) 
                {
                    $request->validate([
                        'car_id' => 'required',
                        'pickup_date' => 'required|date',
                        'amount' => 'required|numeric',
                        'days' => 'required|integer|min:1'
                    ]);
                    $userId = auth()->id();
                    $rentedCar = RentedCar::where('user_id', $userId)
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
                
                    $totalPrice = $car->price * $request->days; // Calculate the total price
                
                    if ($request->amount != $totalPrice) {
                        return response()->json(['message' => 'The amount paid does not match the total price of the car rental'], 400);
                    }
                
                    $car->status = 'rented';
                    $car->pickup_counter += 1; // Increment the pickup count
                    $car->save();
                
                    $rentedCar->status = 'rented';
                    $rentedCar->payment_status = 'paid';
                    $pickupDateTime = Carbon::parse($request->pickup_date)->startOfDay();
                    $rentedCar->pickup_date = $pickupDateTime;
                    $rentedCar->save();
                    $rentedCar->amount = $request->amount;
                    $rentedCar->days = $request->days; // Save the number of days
                    $rentedCar->save();
                
                    return response()->json([   
                        'message' => 'Car picked up successfully.',
                        'Rent' => $rentedCar
                    ], 200);
                }



                public function userReservedCars($userId) // pakita sa  view cars to makikita yugn naka reserved nasasakayan
            {

                $rentedCar = RentedCar::with('car')->find($rentId);


                    return response()->json([
                        'pickup_date' => $rentedCar->pickup_date,
                        'car' => $rentedCar->car,
                        'details' => $rentedCar
                    ]);
            }
            

            public function getUserHistory($userId)
            {   
                $userId = auth()->id();
               
                $rentedCars = RentedCars::where('user_id', $userId)->get();
                $reservedCars = ReservedCars::where('user_id', $userId)->get();
    
                
                return response()->json([
                    'rentedCars' => $rentedCars,
                    'reservedCars' => $reservedCars
                ]);
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



