<?php

use Illuminate\Support\Facades\Route;

Route::get('/',[App\Http\Controllers\DevController::class, 'index']);
Route::get('reset-password', [App\Http\Controllers\DevController::class, 'resetPassword']);
