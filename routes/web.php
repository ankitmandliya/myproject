<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\LeaveTypeController;

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

Route::POST('/register-user', [UserController::class, 'store'])->name('register-user');

Route::POST('/login-user', [UserController::class, 'login'])->name('UserLogin');

Route::get('/dashboard', function () {
    return view('adminpanel.dashboard');
})->name('dashboard')->middleware(AuthenticateUser::class);

Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/page-not-found', function () {
    return view('Loginscreen.pageNotFound');
})->name('pageNotFound');

Route::fallback(function () {
    return redirect()->route('pageNotFound');
});

// HRMS Leaves Routes
Route::get('/leavepolicy', [LeaveTypeController::class, 'index'])->name('leavepolicy')
->middleware(AuthenticateUser::class);

Route::get('/leave-types/create', [LeaveTypeController::class, 'create'])->name('leave-types.create')
->middleware(AuthenticateUser::class);

Route::post('/leave-types', [LeaveTypeController::class, 'store'])->name('leave-types.store')
->middleware(AuthenticateUser::class);

Route::get('/leave-types/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('leave-types.edit')
->middleware(AuthenticateUser::class);

Route::put('/leave-types/{leaveType}', [LeaveTypeController::class, 'update'])->name('leave-types.update')
->middleware(AuthenticateUser::class);

Route::delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('leave-types.destroy')
->middleware(AuthenticateUser::class);