<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::post('/location', [LocationController::class, 'store']);

Route::get('/location/search/radius', [LocationController::class, 'list']);
Route::get('/location/search/delivery-distance', [LocationController::class, 'search']);
