<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\AttendanceServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\StoreAttendanceRequest;
use App\Http\Requests\HRMS\UpdateAttendanceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS attendance operations.
 */
class AttendanceController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected AttendanceServiceInterface $attendanceService
    ) {
    }

    /** Display attendance listing. */
    public function index(): View
    {
        $attendance = $this->attendanceService->paginate();

        return view('Adminpanel.HRMS.Attendance.index', compact('attendance'));
    }

    /** Show attendance creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Attendance.create');
    }

    /** Store attendance. */
    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->attendanceService->markCheckIn((int) $data['user_id'], $data);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance saved successfully.');
    }

    /** Display attendance record. */
    public function show(int $id): View
    {
        $attendance = $this->attendanceService->getById($id);

        return view('Adminpanel.HRMS.Attendance.show', compact('attendance'));
    }

    /** Show attendance edit form. */
    public function edit(int $id): View
    {
        $attendance = $this->attendanceService->getById($id);

        return view('Adminpanel.HRMS.Attendance.edit', compact('attendance'));
    }

    /** Update attendance. */
    public function update(UpdateAttendanceRequest $request, int $id): RedirectResponse
    {
        $this->attendanceService->getById($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance updated successfully.');
    }

    /** Delete attendance. */
    public function destroy(int $id): RedirectResponse
    {
        $this->attendanceService->deleteAttendance($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance deleted successfully.');
    }

    /** Mark employee check-in. */
    public function checkIn(StoreAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->attendanceService->markCheckIn((int) $data['user_id'], $data);

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-in marked successfully.');
    }

    /** Mark employee check-out. */
    public function checkOut(int $userId): RedirectResponse
    {
        $this->attendanceService->markCheckOut($userId);

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-out marked successfully.');
    }

    /** Display monthly attendance report. */
    public function monthlyReport(int $userId, int $month, int $year): View
    {
        $attendance = $this->attendanceService->getMonthlyAttendance($userId, $month, $year);

        return view('Adminpanel.HRMS.Attendance.monthly-report', compact('attendance'));
    }
}
