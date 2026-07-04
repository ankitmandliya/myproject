<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\DashboardServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Controller for the HRMS dashboard.
 */
class DashboardController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {
    }

    /** Display the HRMS dashboard. */
    public function index(): View
    {
        $dashboard = $this->dashboardService->getDashboardWidgets();

        return view('Adminpanel.HRMS.dashboard', compact('dashboard'));
    }
}
