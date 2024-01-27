<?php
use App\Http\Controllers\RentedCarsController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware'=> ['auth:sanctum']],function(){

   Route::get('/user', [AuthController::class, 'getUser']);
   Route::post('/logout',[AuthController::class,'logout']);

   
      //cars

   Route::get('cars/mostrented', [CarController::class, 'mostRentedCars']); //added to flutter DONEE
   Route::get('/cars', [CarController::class, 'index']);//added to flutter DONEE
   Route::post('/cars', [CarController::class, 'store']); 
   Route::get('/cars/{id}', [CarController::class, 'show']);
   Route::put('/cars/{id}', [CarController::class, 'update']);
   Route::delete('/cars/{id}', [CarController::class, 'deleteCar']);
   Route::post('/cars/restore/{id}', [CarController::class, 'restoreCar']);
   Route::get('/available', [CarController::class, 'availableCars']);   //added to flutter DONEE


   //rented

   Route::get('/rent/history', [RentedCarsController::class, 'userRentalHistory']); //add to flutter  NAKAREADY NA PAGE NETO SAME DESIGN NALANG DIN SA VIEW CARS
   Route::post('rent/{id}/cancel', [RentedCarsController::class, 'Cancelreserve']); 
   Route::get('/rentcars', [RentedCarsController::class, 'getRentedCars']);  
   Route::get('/rent', [RentedCarsController::class, 'index']);  
   Route::put('rent/{id}/return', [RentedCarsController::class,'returnCar']); // add to flutter CARDETAILS LOOK A LIKE WITH DUE FEE NA IRERENDER
   Route::post('rent/{id}/pickup', [RentedCarsController::class, 'pickupCar']); // add to flutter done
   Route::post('rent/{id}/reserve', [RentedCarsController::class, 'reserveCar']); ///added to flutter DONEEEE
   Route::post('/user/reserveCars', [RentedCarsController::class, 'userReservedCars']); //added to flutter DONEEEE
   
    
   
   
   
});