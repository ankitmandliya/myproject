<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HRMS\AttendanceController as HrmsAttendanceController;
use App\Http\Controllers\HRMS\DashboardController as HrmsDashboardController;
use App\Http\Controllers\HRMS\FinancialYearController as HrmsFinancialYearController;
use App\Http\Controllers\HRMS\CompanySettingController as HrmsCompanySettingController;
use App\Http\Controllers\HRMS\LeaveApplyController as HrmsLeaveApplyController;
use App\Http\Controllers\HRMS\LeaveReportController as HrmsLeaveReportController;
use App\Http\Controllers\HRMS\NotificationController as HrmsNotificationController;
use App\Http\Controllers\HRMS\RoleController as HrmsRoleController;
use App\Http\Controllers\HRMS\SalaryController as HrmsSalaryController;
use App\Http\Controllers\HRMS\UserController as HrmsUserController;

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
    return view('LoginScreen.signup');
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

// HRMS Module Routes
Route::prefix('hrms')
    ->name('hrms.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('dashboard', [HrmsDashboardController::class, 'index'])->name('dashboard');
        Route::get('reporting-hierarchy', [HrmsUserController::class, 'reportingHierarchy'])->name('reporting-hierarchy.index');

        Route::resource('users', HrmsUserController::class);
        Route::get('attendance/reports', [HrmsAttendanceController::class, 'reports'])->name('attendance.reports');
        Route::get('attendance/reports/employees', [HrmsAttendanceController::class, 'employeeReport'])->name('attendance.reports.employees');
        Route::get('attendance/reports/departments', [HrmsAttendanceController::class, 'departmentReport'])->name('attendance.reports.departments');
        Route::get('attendance/reports/monthly', [HrmsAttendanceController::class, 'reportingMonthlyReport'])->name('attendance.reports.monthly');        Route::get('attendance/widget', [HrmsAttendanceController::class, 'widget'])->name('attendance.widget');
        Route::post('attendance/check-in', [HrmsAttendanceController::class, 'widgetCheckIn'])->name('attendance.check-in');
        Route::post('attendance/check-out', [HrmsAttendanceController::class, 'widgetCheckOut'])->name('attendance.check-out');        Route::get('my-attendance', [HrmsAttendanceController::class, 'myAttendance'])->name('my-attendance');
        Route::get('attendance/employee/{employeeId}', [HrmsAttendanceController::class, 'employeeAttendance'])->name('attendance.employee');
        Route::get('attendance/history/{employeeId}', [HrmsAttendanceController::class, 'history'])->name('attendance.history');
        Route::get('attendance/calendar/{employeeId}', [HrmsAttendanceController::class, 'calendar'])->name('attendance.calendar');
        Route::resource('attendance', HrmsAttendanceController::class);
        Route::post('leave/calculate', [HrmsLeaveApplyController::class, 'calculate'])->name('leave.calculate');
        Route::get('leave-apply/approvals', [HrmsLeaveApplyController::class, 'approvals'])->name('leave-apply.approvals');
        Route::get('leave-apply/calendar', [HrmsLeaveApplyController::class, 'calendar'])->name('leave-apply.calendar');
        Route::post('leave-apply/{leave_apply}/approve', [HrmsLeaveApplyController::class, 'approve'])->name('leave-apply.approve');
        Route::post('leave-apply/{leave_apply}/reject', [HrmsLeaveApplyController::class, 'reject'])->name('leave-apply.reject');
        Route::post('leave-apply/{leave_apply}/cancel', [HrmsLeaveApplyController::class, 'cancel'])->name('leave-apply.cancel');
        Route::post('leave-apply/{leave_apply}/revoke', [HrmsLeaveApplyController::class, 'revoke'])->name('leave-apply.revoke');
        Route::resource('leave-apply', HrmsLeaveApplyController::class);
        Route::get('financial-year', [HrmsFinancialYearController::class, 'index'])->name('financial-year.index');
        Route::get('financial-year/preview', [HrmsFinancialYearController::class, 'preview'])->name('financial-year.preview');
        Route::post('financial-year/close', [HrmsFinancialYearController::class, 'close'])->name('financial-year.close');
        Route::get('financial-year/history', [HrmsFinancialYearController::class, 'history'])->name('financial-year.history');
        Route::get('financial-year/{closing}', [HrmsFinancialYearController::class, 'show'])->name('financial-year.show');
        Route::post('financial-year/{closing}/reopen', [HrmsFinancialYearController::class, 'reopen'])->name('financial-year.reopen');
        Route::get('leave-reports', [HrmsLeaveReportController::class, 'index'])->name('leave-reports.index');
        Route::get('leave-reports/employee', [HrmsLeaveReportController::class, 'employee'])->name('leave-reports.employee');
        Route::get('leave-reports/employee/{employee}', [HrmsLeaveReportController::class, 'employeeShow'])->name('leave-reports.employee.show');
        Route::get('leave-reports/department', [HrmsLeaveReportController::class, 'department'])->name('leave-reports.department');
        Route::get('leave-reports/leave-type', [HrmsLeaveReportController::class, 'leaveType'])->name('leave-reports.leave-type');
        Route::get('leave-reports/balance', [HrmsLeaveReportController::class, 'balance'])->name('leave-reports.balance');
        Route::get('leave-reports/liability', [HrmsLeaveReportController::class, 'liability'])->name('leave-reports.liability');
        Route::get('leave-reports/monthly', [HrmsLeaveReportController::class, 'monthly'])->name('leave-reports.monthly');
        Route::get('leave-reports/financial-year', [HrmsLeaveReportController::class, 'financialYear'])->name('leave-reports.financial-year');
        Route::get('leave-reports/approval', [HrmsLeaveReportController::class, 'approval'])->name('leave-reports.approval');
        Route::get('leave-reports/lwp', [HrmsLeaveReportController::class, 'lwp'])->name('leave-reports.lwp');
        Route::get('leave-reports/sandwich', [HrmsLeaveReportController::class, 'sandwich'])->name('leave-reports.sandwich');
        Route::get('notifications', [HrmsNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [HrmsNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/{notification}', [HrmsNotificationController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{notification}/read', [HrmsNotificationController::class, 'markRead'])->name('notifications.read');
        Route::resource('salary', HrmsSalaryController::class);
        Route::resource('roles', HrmsRoleController::class);

        Route::get('company-setting', [HrmsCompanySettingController::class, 'index'])
            ->name('company-setting.index');
        Route::put('company-setting', [HrmsCompanySettingController::class, 'updateSettings'])
            ->name('company-setting.update');
    });

Route::fallback(function () {
    return redirect()->route('pageNotFound');
});
