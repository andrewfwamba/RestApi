<?php

use App\Http\Controllers\Api\MusicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Auth;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/auth/register', [UserController::class, 'createUser']);
Route::post('/auth/login', [UserController::class, 'loginUser']);
Route::post('/password/email', [UserController::class, 'sendResetToken'])->name("password.reset");
Route::get('/ping', function () {
    return response()->json(['success' => true]);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get/music/all',  [MusicController::class, 'index']);
    Route::get('/get/music/{id}',  [MusicController::class, 'show']);
    Route::post('/music/add',  [MusicController::class, 'store']);
    Route::delete('/music/delete/{id}', [MusicController::class, 'destroy']);
    Route::patch('/music/update/{id}', [MusicController::class, 'update']);
    Route::get('/stream-music/{id}', [MusicController::class, 'stream']);
});
