<?php

use App\Http\Controllers\EventsTblController;
use App\Http\Controllers\SwapRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes (JWT required)
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/insert-events', [EventsTblController::class, 'insertEvent']);
    Route::get('/user-events', [EventsTblController::class, 'fetchEvents']);
    Route::patch('/events/{id}', [EventsTblController::class, 'updateEventStatus']);
    Route::get('/swappable-slots', [EventsTblController::class, 'fetchSwappableSlots']);
    Route::get('/user/swappable-slots', [EventsTblController::class, 'fetchMySwappableSlots']);
    Route::post('/swap-request', [SwapRequestController::class, 'createSwapRequest']);
    Route::post('/swap-response', [SwapRequestController::class, 'respondToSwap']);
    Route::get('/swap-requests/incoming', [SwapRequestController::class, 'incoming']);
    Route::get('/swap-requests/outgoing', [SwapRequestController::class, 'outgoing']);
});