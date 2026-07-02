<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveTypes = LeaveType::where('status', 1)
        ->orderby('leave_name', 'asc')
        ->paginate(4);
        return view('Adminpanel.HRMS.Leaves.leavepolicy', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Adminpanel.HRMS.Leaves.addLeaveType');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Debugging line to check incoming request data
        $request->validate([
            'leave_name' => 'required|string|max:255',
            'leave_code' => 'required|string|max:255|unique:leave_types,leave_code',
            'total_days' => 'required|integer|min:1',
            'is_paid' => 'required',
        ]);

        try {
            LeaveType::create([
                'leave_name' => $request->leave_name,
                'leave_code' => $request->leave_code,
                'total_days' => $request->total_days,
                'is_paid' => $request->is_paid,
                'status' => 1, // Assuming new leave types are active by default
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create leave type: ' . $e->getMessage());
        }

        return redirect()->route('leavepolicy')->with('success', 'Leave type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        return view('Adminpanel.HRMS.Leaves.updateLeaveType', compact('leaveType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'leave_name' => 'required|string|max:255',
            'leave_code' => 'required|string|max:255|unique:leave_types,leave_code,' . $leaveType->id,
            'total_days' => 'required|integer|min:1',
            'is_paid' => 'required',
        ]);

        try {
            $leaveType->update([
                'leave_name' => $request->leave_name,
                'leave_code' => $request->leave_code,
                'total_days' => $request->total_days,
                'is_paid' => $request->is_paid,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update leave type: ' . $e->getMessage());
        }

        return redirect()->route('leavepolicy')->with('success', 'Leave type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        try {
            $leaveType->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete leave type: ' . $e->getMessage());
        }

        return redirect()->route('leavepolicy')->with('success', 'Leave type deleted successfully.');
    }
}
