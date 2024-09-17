<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix("/v1")->group(function () {

    Route::prefix("/User")->group(function () {
        Route::post('/', [UserController::class, 'store']);
        Route::get('/', [UserController::class, 'users']);
        Route::delete('/{id}', [UserController::class, 'delete']); // Corrected route parameter
    });

    Route::prefix("/Profile")->group(function () {
        Route::get('/{id}', [UserController::class, 'user']);
    });


    Route::prefix("/Auth")->group(function () {
        Route::post('/', [UserController::class, 'login']);
    });

    Route::prefix("/GoogleAuth")->group(function () {
        Route::post('/', [GoogleController::class, 'loginOrRegister']);
    });

    Route::prefix("/Score")->group(function () {
        Route::post('/', [ScoreController::class, 'store']);
        Route::get('/{id}', [ScoreController::class, 'scores']);
        Route::get('/', [ScoreController::class, 'allScores']);
    });

    Route::prefix("/Leaderboard")->group(function () {
        Route::get('/', [ScoreController::class, 'allScores']);
    });

    Route::prefix("/Payment")->group(function () {
        Route::post('/', [PaymentController::class, 'createPaymentIntent']);
    });

    Route::prefix("/Image")->group(function () {
        Route::post('/{id}', [UserController::class, 'image']);
    });

});
