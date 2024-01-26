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
                    $car->pickup_counter += 1;
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





            //getRentedCars

            public function getRentedCars()
            {
                $userId = auth()->id();

                $rentedCars = RentedCar::with('car')
                    ->where('user_id', $userId)
                    ->where('status', 'rented')
                    ->get();
            
                // Format the response to include only the necessary car details
                $rentedCars = $rentedCars->map(function ($rentedCar) {
                    $pickupDate = \Carbon\Carbon::parse($rentedCar->pickup_date);
                    $returnDate = $pickupDate->copy()->addDays($rentedCar->days);
            
                    return [
                        'car_id' => $rentedCar->car_id,
                        'car_image' => $rentedCar->car->image,
                        'car_name' => $rentedCar->car->name,
                        'pickup_date' => $pickupDate->toDateString(),
                        'return_date' => $returnDate->toDateString(),
                        'days' => $rentedCar->days,
                        'car_brand' => $rentedCar->car->brand,
                        'due_fee' => $rentedCar->due_fee,
                    ];
                });
            
                return response()->json([
                    'message' => 'Rented cars retrieved successfully.',
                    'rented_cars' => $rentedCars
                ]);
            }


                //pickupCar
                public function pickUpCar(Request $request, $car_id)
                {
                    $userId = auth()->id();
                    $days = $request->input('days');
                    $pickupDate = $request->input('pickup_date');
                
                    DB::beginTransaction();
                
                    try {
                        $car = Car::find($car_id);
                        if (!$car) {
                            return response()->json([
                                'message' => 'No car found with the given ID.'
                            ], 404);
                        }
                
                        if ($car->status != 'reserved') {
                            return response()->json([
                                'message' => 'This car is not reserved and cannot be rented.'
                            ], 400);
                        }
                
                        // Calculate the total price
                        $totalPrice = $days * $car->price;
                
                        // Find the RentedCar record with status 'to pickup'
                        $rentedCar = RentedCar::where('user_id', $userId)
                            ->where('car_id', $car_id)
                            ->where('status', 'topickup')
                            ->first();
                
                        if (!$rentedCar) {
                            return response()->json([
                                'message' => 'No rented car found with the given ID for the logged in user.'
                            ], 404);
                        }
                
                        // Update the RentedCar record
                        $rentedCar->days = $days;
                        $rentedCar->pickup_date = $pickupDate;
                        $rentedCar->status = 'rented';
                        $rentedCar->amount = $totalPrice;
                        $rentedCar->save();
                
                        // Update the car's status to rented
                        $car->status = 'rented';
                        $car->save();
                
                        DB::commit();
                
                        return response()->json([
                            'message' => 'Car picked up successfully.',
                            'total_price' => $totalPrice
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();
                
                        return response()->json([
                            'message' => 'Failed to pick up car: ' . $e->getMessage()
                        ], 500);
                    }
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



              public function Cancelreserve($carId)
              {
                $userId = Auth::id();
            
                $car = Car::find($carId);
            
                if (!$car) {
                    return response()->json(['message' => 'Car not found'], 404);
                }
            
                if ($car->status != 'reserved') {
                    return response()->json(['message' => 'No record of reservation'], 400);
                }
            
                    $car->status = 'available'; 
                    $car->save();
            
                $rentedCar = RentedCar::where('car_id', $carId)
                    ->where('user_id', $userId)
                    ->where('status', 'topickup')
                    ->first();
            
                if (!$rentedCar) {
                    return response()->json(['message' => 'No rented car found for this user and car'], 404);
                }
            
                $rentedCar->status = 'cancelled';
                $rentedCar->pickup_date = now();
                $rentedCar->return_date = now();
                $rentedCar->save();
            
                return response()->json([
                    'message' => 'Car reservation cancelled successfully.',
                    'rentedCar' => $rentedCar
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



