<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:register')->post('/register', [UserController::class, 'register'])->name('api.users.register');
