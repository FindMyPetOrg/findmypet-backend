<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'as' => 'api.',
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['auth:sanctum']
], function () {
    Route::apiResource('users', 'UserProfileController')->except('index', 'store');
    Route::delete('users/forceDelete/{user}', 'UserProfileController@forceDelete')
        ->name('users.forceDelete');
    Route::patch('users/restore/{user}', 'UserProfileController@restore')
        ->name('users.restore');
    Route::patch('users/profilePhoto/{user}', 'UserProfileController@profilePhoto')
        ->name('users.profilePhoto');
    Route::delete('users/deleteProfilePhoto/{user}', 'UserProfileController@deleteProfilePhoto')
        ->name('users.deleteProfilePhoto');
});
