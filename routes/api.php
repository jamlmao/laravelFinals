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
   Route::get('cars/mostrented', [CarController::class, 'mostRentedCars']); //Check
   Route::get('/cars', [CarController::class, 'index']);//C
   Route::post('/cars', [CarController::class, 'store']); 
   Route::get('/cars/{id}', [CarController::class, 'show']);
   Route::put('/cars/{id}', [CarController::class, 'update']);
   Route::delete('/cars/{id}', [CarController::class, 'deleteCar']);
   Route::post('/cars/restore/{id}', [CarController::class, 'restoreCar']);
   Route::get('/available', [CarController::class, 'availableCars']);   //Check
   //rented

   Route::get('user/{id}/rent/history', [RentedCarsController::class, 'getUserHistory']);
   Route::get('/rent', [RentedCarsController::class, 'index']);  
   Route::put('user/{id}/rent/return', [RentedCarsController::class,'returnCar']); // new shit
   Route::post('rent/pickup', [RentedCarsController::class, 'pickupCar']); // new shit
   Route::post('rent/{id}/reserve', [RentedCarsController::class, 'reserveCar']); //Check 
   Route::post('/user/reserveCars', [RentedCarsController::class, 'userReservedCars']); //Check 
   
    
   
   
   
});