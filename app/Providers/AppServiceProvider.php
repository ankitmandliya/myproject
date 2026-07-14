<?php

namespace App\Providers;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\Common\CommonServiceInterface;
use App\Contracts\Common\DateServiceInterface;
use App\Contracts\Common\EmployeeCodeServiceInterface;
use App\Contracts\Common\FileUploadServiceInterface;
use App\Contracts\Common\PaginationServiceInterface;
use App\Contracts\Common\ResponseServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\DashboardServiceInterface;
use App\Contracts\FinancialYearClosingServiceInterface;
use App\Contracts\HolidayServiceInterface;
use App\Contracts\LeaveApprovalServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\LeaveReportServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Contracts\NotificationServiceInterface;
use App\Contracts\LeaveTypeServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Contracts\RoleServiceInterface;
use App\Contracts\SalaryServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Services\AttendanceService;
use App\Services\Common\CommonService;
use App\Services\Common\DateService;
use App\Services\Common\EmployeeCodeService;
use App\Services\Common\FileUploadService;
use App\Services\Common\PaginationService;
use App\Services\Common\ResponseService;
use App\Services\CompanySettingService;
use App\Services\DashboardService;
use App\Services\FinancialYearClosingService;
use App\Services\HolidayService;
use App\Services\LeaveApprovalService;
use App\Services\LeavePolicyService;
use App\Services\LeaveReportService;
use App\Services\LeaveService;
use App\Services\LeaveTypeService;
use App\Services\NotificationService;
use App\Services\RolePermissionService;
use App\Services\RoleService;
use App\Services\SalaryService;
use App\Services\UserService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(LeavePolicyServiceInterface::class, LeavePolicyService::class);
        $this->app->bind(LeaveReportServiceInterface::class, LeaveReportService::class);
        $this->app->bind(LeaveApprovalServiceInterface::class, LeaveApprovalService::class);
        $this->app->bind(LeaveServiceInterface::class, LeaveService::class);
        $this->app->bind(SalaryServiceInterface::class, SalaryService::class);
        $this->app->bind(RolePermissionServiceInterface::class, RolePermissionService::class);
        $this->app->bind(CompanySettingServiceInterface::class, CompanySettingService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(HolidayServiceInterface::class, HolidayService::class);
        $this->app->bind(LeaveTypeServiceInterface::class, LeaveTypeService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(FinancialYearClosingServiceInterface::class, FinancialYearClosingService::class);
        $this->app->bind(CommonServiceInterface::class, CommonService::class);
        $this->app->bind(DateServiceInterface::class, DateService::class);
        $this->app->bind(FileUploadServiceInterface::class, FileUploadService::class);
        $this->app->bind(PaginationServiceInterface::class, PaginationService::class);
        $this->app->bind(EmployeeCodeServiceInterface::class, EmployeeCodeService::class);
        $this->app->bind(ResponseServiceInterface::class, ResponseService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}





