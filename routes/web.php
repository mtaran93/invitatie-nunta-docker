<?php

use App\Http\Controllers\WrittenGuestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WrittenGuestController::class, 'index'])->name('invitation');
Route::post('/answer', [WrittenGuestController::class, 'store'])->name('answer')
    ->middleware('throttle:answer');;
