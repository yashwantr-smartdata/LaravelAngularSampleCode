<?php

use Illuminate\Http\Request;
use App\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//User authentication
Route::post('/user/login','API\UserController@authenticateUser');


Route::get('/unAuthenticated',function(){
	return response()->json([
		'status' => 'error',
		'message' =>'unAuthenticated Attempt'
	],200);
})->name('login');	

// Social Login Api
Route::post('/user/socialLoginGoogle','API\UserController@socialLoginWithGoogle');
Route::post('/user/socialLoginFb','API\UserController@socialLoginWithFb');

// Get Items detail
Route::post('/customer/item_details','API\ItemsController@item_details');

//Passport Auth:API middleware
Route::middleware('auth:api')->group(function () {
	// Save customer information
	Route::post('/customer/save_customer','API\CustomerController@save_customer');\
});

