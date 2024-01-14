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

   Route::post('/logout',[AuthController::class,'logout']);
      //cars
   Route::get('/cars', [CarController::class, 'index']);
   Route::post('/cars', [CarController::class, 'store']);
   Route::get('/cars/{id}', [CarController::class, 'show']);
   Route::put('/cars/{id}', [CarController::class, 'update']);
  Route::delete('/cars/{id}', [CarController::class, 'deleteCar']);
  Route::post('/cars/restore/{id}', [CarController::class, 'restoreCar']);

   //rented
   Route::get('/rent', [RentedCarsController::class, 'index']);
   Route::post('/rent', [RentedCarsController::class, 'store']);
   Route::put('user/{id}/rented-cars/return', [RentedCarsController::class, 'returnCar']);
   Route::get('/users/{userId}/rent', [UserController::class, 'userRentedCars']);
   
   
   
});