<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;


class HolidayController extends Controller
{
    public function index()
    {
        //to view all holidays on the viewHoliday page
        $holidays = Holiday::active(1) //active is local scope defined in Holiday model to filter holidays based on status and 1 is the value for active status, so it will return only the holidays with status 1 (active)
        ->get();
        return view('Adminpanel.HRMS.Leaves.viewHoliday', compact('holidays'));
    }

    public function create()
    {
        //to show the form for adding a new holiday
        return view('Adminpanel.HRMS.Leaves.addHoliday');
    }

    public function store(Request $request)
    {
        //to store a new holiday in the database
        $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'status' => 'required|in:0,1'
        ]);

        try {
            Holiday::create([
                'name' => $request->input('name'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'status' => $request->input('status')
            ]);

            return redirect()->route('holidays.index')->with('success', 'Holiday added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while adding the holiday. Please try again.');
        }
    }

    public function edit(Holiday $holiday)
    {
        //to show the form for editing an existing holiday
        $records = Holiday::where('id', $holiday->id)->first();
        return view('Adminpanel.HRMS.Leaves.updateHoliday', compact('records'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        //to update an existing holiday in the database
        $request->validate([
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'status' => 'required|in:0,1'
        ]);

        try {
            $holiday->update([
                'name' => $request->input('name'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'status' => $request->input('status')
            ]);

            return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the holiday. Please try again.');
        }
    }

    public function destroy(Holiday $holiday)
    {
        //to delete an existing holiday from the database
        try {
            $holiday->delete();
            return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the holiday. Please try again.');
        }
    }
    
}
