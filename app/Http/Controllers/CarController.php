<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Http\Resources\CarResource;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        return response([
            'cars' => Car::select('id', 'name', 'desc','price','image','brand','status','pickup_counter')->get()
        ], 200);
    }


    public function show($id)
    {
        return response([
            'cars' => Car::where('id',$id)->get()
        ],200);
    }



     public function store(Request $request)
    {
        $request->validate([
            'desc' => 'required',
            'name' => 'required',
            'brand' => 'required',
            'price' => 'required',
            'image' => 'required|string', 
        ]);

        $imageName = time().'.png';  
        
      
        $decodedImage = base64_decode($request->image, true);

        if ($decodedImage === false) {
            return response([
                'message' => 'Invalid base64 image'
            ], 400);
        }

        $isSaved = \Storage::disk('public')->put('images/'.$imageName, $decodedImage);

        if (!$isSaved) {
            return response([
                'message' => 'Failed to save image'
            ], 500);
        }

        $car = Car::create([
            'desc' => $request->desc,
            'name' => $request->name,
            'brand' => $request->brand,
            'price' => $request->price,
            'image' => asset('storage/images/'.$imageName),
            'status' => 'available' ,
            'pickup_count' => 0
        ]);

        return response([
            'message' => 'Car created successfully',
            'car' => $car
        ], 200);
    }

        public function mostRentedCars() 
        {
            $cars = Car::orderBy('pickup_counter', 'desc')->take(3)->get();
        
            return response()->json(['cars' => $cars], 200);
        }

     public function update(Request $request, $id)
    {
        $car = Car::find($id);
    
        $attrs = $request->validate([
            'brand' => 'sometimes|string',
            'name' => 'sometimes|string',
            'desc' => 'sometimes|string',
            'price'=> 'sometimes|numeric',
            'image' => 'sometimes|string', 
        ]);
    
        if ($request->get('image')) {
            $imageName = time().'.png';  
    
            
            $decodedImage = base64_decode($request->get('image'), true);
    
            if ($decodedImage === false) {
                return response([
                    'message' => 'Invalid base64 image'
                ], 400);
            }
    
            
            $isSaved = \Storage::disk('public')->put('images/'.$imageName, $decodedImage);
    
            if (!$isSaved) {
                return response([
                    'message' => 'Failed to save image'
                ], 500);
            }
    
            $attrs['image'] = asset('storage/images/'.$imageName);
        }
    
        $car->update($attrs);
    
        return response([ 
            'message' => 'Car updated successfully',
            'car' => $car
        ], 200);
    }
    


    public function deleteCar($id)
    {
        $car = Car::find($id);
    
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    
        $car->delete();
    
        return response()->json(['message' => 'Car deleted successfully']);
    }

    public function restoreCar($id)
    {
        $car = Car::withTrashed()->find($id);
    
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    
        $car->restore();
    
        return response()->json(['message' => 'Car restored successfully']);
    }
    
    public function availableCars()
        {
            return response([
                'cars' => Car::select('id', 'name', 'desc','price','image','brand','status','pickup_counter')
                             ->where('status', 'available')
                             ->get()
            ], 200);
        }
        
       
}