<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\SalaryServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\GenerateSalaryRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for HRMS salary operations.
 */
class SalaryController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected SalaryServiceInterface $salaryService
    ) {
    }

    /** Display salary slip listing. */
    public function index(): View
    {
        $salarySlips = $this->salaryService->paginate();

        return view('Adminpanel.HRMS.Salary.index', compact('salarySlips'));
    }

    /** Show salary generation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Salary.create');
    }

    /** Store salary generation request. */
    public function store(GenerateSalaryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->salaryService->generateMonthlySalary((int) $data['user_id'], (int) $data['month'], (int) $data['year']);

        return redirect()->route('hrms.salary.index')->with('success', 'Salary generated successfully.');
    }

    /** Display a salary slip. */
    public function show(int $id): View
    {
        $salarySlip = $this->salaryService->getById($id);

        return view('Adminpanel.HRMS.Salary.show', compact('salarySlip'));
    }

    /** Show salary edit form. */
    public function edit(int $id): View
    {
        $salarySlip = $this->salaryService->getById($id);

        return view('Adminpanel.HRMS.Salary.edit', compact('salarySlip'));
    }

    /** Update salary details. */
    public function update(FormRequest $request, int $id): RedirectResponse
    {
        $request->validated();
        $this->salaryService->getById($id);

        return redirect()->route('hrms.salary.index')->with('success', 'Salary updated successfully.');
    }

    /** Delete salary details. */
    public function destroy(int $id): RedirectResponse
    {
        $this->salaryService->deleteSalarySlip($id);

        return redirect()->route('hrms.salary.index')->with('success', 'Salary deleted successfully.');
    }

    /** Generate monthly salary. */
    public function generate(GenerateSalaryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->salaryService->generateMonthlySalary((int) $data['user_id'], (int) $data['month'], (int) $data['year']);

        return redirect()->route('hrms.salary.index')->with('success', 'Salary generated successfully.');
    }

    /** Download a salary slip. */
    public function downloadSlip(int $userId, string $month): Response
    {
        $this->salaryService->getSalarySlip($userId, $month);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
