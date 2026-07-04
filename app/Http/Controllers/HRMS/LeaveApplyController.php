<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\LeaveServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\ApproveLeaveRequest;
use App\Http\Requests\HRMS\RejectLeaveRequest;
use App\Http\Requests\HRMS\StoreLeaveRequest;
use App\Http\Requests\HRMS\UpdateLeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS leave application operations.
 */
class LeaveApplyController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected LeaveServiceInterface $leaveService
    ) {
    }

    /** Display leave application listing. */
    public function index(): View
    {
        $leaves = $this->leaveService->paginate();

        return view('Adminpanel.HRMS.Leaves.index', compact('leaves'));
    }

    /** Show leave application form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Leaves.create');
    }

    /** Store a leave application. */
    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->leaveService->applyLeave((int) ($data['user_id'] ?? 0), $data);

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave applied successfully.');
    }

    /** Display a leave application. */
    public function show(int $id): View
    {
        $leave = $this->leaveService->getById($id);

        return view('Adminpanel.HRMS.Leaves.show', compact('leave'));
    }

    /** Show leave edit form. */
    public function edit(int $id): View
    {
        $leave = $this->leaveService->getById($id);

        return view('Adminpanel.HRMS.Leaves.edit', compact('leave'));
    }

    /** Update a leave application. */
    public function update(UpdateLeaveRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        $this->leaveService->validateLeave((int) ($data['user_id'] ?? 0), $data);

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave updated successfully.');
    }

    /** Delete a leave application. */
    public function destroy(int $id): RedirectResponse
    {
        $this->leaveService->deleteLeave($id);

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave deleted successfully.');
    }

    /** Show leave apply form. */
    public function apply(): View
    {
        return view('Adminpanel.HRMS.Leaves.apply');
    }

    /** Approve a leave application. */
    public function approve(ApproveLeaveRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        $this->leaveService->approveLeave($id, isset($data['approved_by']) ? (int) $data['approved_by'] : null);

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave approved successfully.');
    }

    /** Reject a leave application. */
    public function reject(RejectLeaveRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        $this->leaveService->rejectLeave($id, isset($data['approved_by']) ? (int) $data['approved_by'] : null);

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave rejected successfully.');
    }

    /** Display leave history. */
    public function history(): View
    {
        $leaves = $this->leaveService->paginate();

        return view('Adminpanel.HRMS.Leaves.history', compact('leaves'));
    }
}
