<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\HolidayController;

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
    return view('LoginScreen.login');
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
    return view('Adminpanel.dashboard');
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

// HRMS Holidays Routes

Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index')
->middleware(AuthenticateUser::class); //to open view holiday page

Route::get('/holidays/create', [HolidayController::class, 'create'])->name('holidays.create')
->middleware(AuthenticateUser::class); // to open add holiday form

Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store')
->middleware(AuthenticateUser::class); // insert holiday data into database

Route::get('/holidays/{holiday}/edit', [HolidayController::class, 'edit'])->name('holidays.edit')
->middleware(AuthenticateUser::class); //to open edit form with userid

Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update')
->middleware(AuthenticateUser::class); // to update holiday data into database

Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy')
->middleware(AuthenticateUser::class); // to delete holiday data from database