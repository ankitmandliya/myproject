<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\HolidayServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS holiday operations.
 */
class HolidayController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected HolidayServiceInterface $holidayService
    ) {
    }

    /** Display holiday listing. */
    public function index(): View
    {
        $holidays = $this->holidayService->paginate();

        return view('Adminpanel.HRMS.Holidays.index', compact('holidays'));
    }

    /** Show holiday creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Holidays.create');
    }

    /** Store a holiday. */
    public function store(FormRequest $request): RedirectResponse
    {
        $this->holidayService->store($request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    /** Display a holiday. */
    public function show(int $id): View
    {
        $holiday = $this->holidayService->getById($id);

        return view('Adminpanel.HRMS.Holidays.show', compact('holiday'));
    }

    /** Show holiday edit form. */
    public function edit(int $id): View
    {
        $holiday = $this->holidayService->getById($id);

        return view('Adminpanel.HRMS.Holidays.edit', compact('holiday'));
    }

    /** Update a holiday. */
    public function update(FormRequest $request, int $id): RedirectResponse
    {
        $this->holidayService->update($id, $request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    /** Delete a holiday. */
    public function destroy(int $id): RedirectResponse
    {
        $this->holidayService->delete($id);

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
