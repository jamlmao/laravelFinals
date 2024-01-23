<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
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
            'status' => 'topickup'  
        ]);
    
        return response()->json([
            'message' => 'Car reserved successfully.',
            'rentedCar' => $rentedCar
        ], 200);
    }
    
       

    

            //returnCar
            public function returnCar($car_id)
            {
                $userId = auth()->id();
            
                DB::beginTransaction();
            
                try {
                    $rentedCar = RentedCar::where('user_id', $userId)
                        ->where('car_id', $car_id)
                        ->whereNull('return_date')
                        ->first();
            
                    if (!$rentedCar) {
                        return response()->json([
                            'message' => 'No rented car found with the given ID for the logged in user.'
                        ], 404);
                    }
            
                    // Check if the car is already returned
                    if ($rentedCar->return_date) {
                        return response()->json([
                            'message' => 'The car has already been returned.'
                        ], 400);
                    }
            
                    // Check if the car has been picked up
                    $pickupDate = \Carbon\Carbon::parse($rentedCar->pickup_date);
                    if (!$pickupDate || $pickupDate->isFuture()) {
                        return response()->json([
                            'message' => 'The car has not been picked up yet.'
                        ], 400);
                    }
            
                    $car = Car::find($rentedCar->car_id);
                    if (!$car) {
                        return response()->json([
                            'message' => 'No car found with the given ID.'
                        ], 404);
                    }
            
                    $returnDate = now();
                    $rentedDays = $pickupDate->diffInDays($returnDate);
                    $dueDays = max($rentedDays - $rentedCar->rented_days, 0);
                    $dueFee = $dueDays * $car->price;
            
                    $rentedCar->return_date = $returnDate;
                    $rentedCar->status = "returned";
                    $rentedCar->due_fee = $dueFee;
                    $rentedCar->save();
            
                    $car->status = 'available';
                    $car->save();
            
                    DB::commit();
            
                    return response()->json([
                        'message' => 'Car returned successfully.',
                        'due_fee' => $dueFee
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
            
                    return response()->json([
                        'message' => 'Failed to return car: ' . $e->getMessage()
                    ], 500);
                }
            }
            

                //pickupCar
                public function pickupCar(Request $request, $car_id) 
                {
                    $request->validate([
                        'pickup_date' => 'required|date',
                        'amount' => 'required|numeric',
                        'days' => 'required|integer|min:1'
                    ]);
                    $userId = auth()->id();

                    $rentedCar = RentedCar::where('user_id', $userId)
                                        ->where('car_id', $car_id)
                                        ->where('status', 'topickup')
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
                        return response()->json(['message' => 'Payment exceed or below the total price'], 400);
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



                public function userReservedCars()// pakita sa  view cars to makikita yugn naka reserved nasasakayan
                {
                   
                    $userId = auth()->id();
                
                    
                    $reservedUnpaidCars = RentedCar::with('car')
                        ->where('user_id', $userId)
                        ->whereNull('pickup_date') // Car is reserved
                        ->where('payment_status', 0) // Car is unpaid
                        ->get();
                
                    if ($reservedUnpaidCars->isEmpty()) {
                        return response()->json([
                            'message' => 'No reserved and unpaid cars found for the logged in user.'
                        ], 404);
                    } else {
                        return response()->json([
                            'reservedUnpaidCars' => $reservedUnpaidCars
                        ]);
                    }
                }


            public function userRentalHistory($userId)
                    {
                        // Fetch all rented cars by the user
                        $rentedCars = RentedCar::with('car')
                            ->where('user_id', $userId)
                            ->get();

                        // Return the rented cars as a JSON response
                        return response()->json([
                            'rentedCars' => $rentedCars
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



