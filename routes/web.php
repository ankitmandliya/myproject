<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('Loginscreen.signup');
})->name('signup');

Route::get('/login', function () {
    return view('Loginscreen.login');
})->name('login');

Route::get('/forgot-password', function () {
    return view('Loginscreen.forgotpassword');
})->name('forgot-password');

Route::get('/verification-code', function () {
    return view('Loginscreen.verificationcode');
})->name('verification.code');
