<?php

use App\Http\Controllers\WrittenGuestController;
use Illuminate\Support\Facades\Route;

Route::get('/invitation', [WrittenGuestController::class, 'index'])->name('invitation');
Route::post('/invitation/answer', [WrittenGuestController::class, 'store'])->name('invitation.answer');
