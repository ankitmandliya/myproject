<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\CompanySettingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\UpdateCompanySettingRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS company setting operations.
 */
class CompanySettingController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected CompanySettingServiceInterface $companySettingService
    ) {
    }

    /** Display company settings. */
    public function index(): View
    {
        $settings = $this->companySettingService->getSettings();

        return view('Adminpanel.HRMS.CompanySettings.index', compact('settings'));
    }

    /** Show company setting creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.CompanySettings.create');
    }

    /** Store company settings. */
    public function store(UpdateCompanySettingRequest $request): RedirectResponse
    {
        $this->companySettingService->updateSettings($request->validated());

        return redirect()->route('hrms.company-setting.index')->with('success', 'Company settings saved successfully.');
    }

    /** Display company settings detail. */
    public function show(int $id): View
    {
        $settings = $this->companySettingService->getSettings();

        return view('Adminpanel.HRMS.CompanySettings.show', compact('settings'));
    }

    /** Show company settings edit form. */
    public function edit(int $id): View
    {
        $settings = $this->companySettingService->getSettings();

        return view('Adminpanel.HRMS.CompanySettings.edit', compact('settings'));
    }

    /** Update company settings. */
    public function update(UpdateCompanySettingRequest $request): RedirectResponse
    {
        $this->companySettingService->updateSettings($request->validated());

        return redirect()->route('hrms.company-setting.index')->with('success', 'Company settings updated successfully.');
    }

    /** Delete company settings. */
    public function destroy(int $id): RedirectResponse
    {
        $this->companySettingService->getSettings();

        return redirect()->route('hrms.company-setting.index')->with('success', 'Company settings deleted successfully.');
    }

    /** Update company settings through the dedicated action. */
    public function updateSettings(UpdateCompanySettingRequest $request): RedirectResponse
    {
        $this->companySettingService->updateSettings($request->validated());

        return redirect()->route('hrms.company-setting.index')->with('success', 'Company settings updated successfully.');
    }
}


