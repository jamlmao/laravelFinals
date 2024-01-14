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
            'cars' => Car::select('id', 'name', 'desc','price','image','brand')->get()
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
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $car = new Car([
            'brand' => $request->get('brand'),
            'desc' => $request->get('desc'),
            'name' => $request->get('name'),
            'brand' => $request->get('brand'),
            'price' => $request->get('price'),
        ]);
    
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->storeAs('public/images', $imageName);
            $car->image = asset('storage/images/'.$imageName);
        }
    
        $car->save();
    
        return response()->json(['message' => 'Car created successfully', 'car' => $car]);
    }

    public function update(Request $request, $id)
    {
        $car = Car::find($id);

        $attrs = $request->validate([
            'brand' => 'required|string',
            'name' => 'required|string',
            'desc' => 'sometimes|string',
            'price'=> 'sometimes|string',
        ]);
    
        $attrs = array_filter($attrs, function ($value) {
            return !is_null($value);
        });
    
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
    



}