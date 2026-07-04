<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\LeaveTypeServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS leave type operations.
 */
class LeaveTypeController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected LeaveTypeServiceInterface $leaveTypeService
    ) {
    }

    /** Display leave type listing. */
    public function index(): View
    {
        $leaveTypes = $this->leaveTypeService->paginate();

        return view('Adminpanel.HRMS.LeaveTypes.index', compact('leaveTypes'));
    }

    /** Show leave type creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.LeaveTypes.create');
    }

    /** Store a leave type. */
    public function store(FormRequest $request): RedirectResponse
    {
        $this->leaveTypeService->store($request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    /** Display a leave type. */
    public function show(int $id): View
    {
        $leaveType = $this->leaveTypeService->getById($id);

        return view('Adminpanel.HRMS.LeaveTypes.show', compact('leaveType'));
    }

    /** Show leave type edit form. */
    public function edit(int $id): View
    {
        $leaveType = $this->leaveTypeService->getById($id);

        return view('Adminpanel.HRMS.LeaveTypes.edit', compact('leaveType'));
    }

    /** Update a leave type. */
    public function update(FormRequest $request, int $id): RedirectResponse
    {
        $this->leaveTypeService->update($id, $request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    /** Delete a leave type. */
    public function destroy(int $id): RedirectResponse
    {
        $this->leaveTypeService->delete($id);

        return redirect()->route('leave-types.index')->with('success', 'Leave type deleted successfully.');
    }
}
