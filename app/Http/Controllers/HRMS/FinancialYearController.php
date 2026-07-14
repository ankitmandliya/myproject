<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\FinancialYearClosingServiceInterface;
use App\Http\Controllers\Controller;
use App\Jobs\CloseFinancialYearJob;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FinancialYearController extends Controller
{
    public function __construct(protected FinancialYearClosingServiceInterface $financialYearClosingService) {}

    public function index(Request $request): View
    {
        $this->authorizeRole(['HR', 'Admin']);
        $dashboard = $this->financialYearClosingService->dashboard($request->query('financial_year'));

        return view('Adminpanel.HRMS.FinancialYear.index', $this->viewData($dashboard));
    }

    public function preview(Request $request): View
    {
        $this->authorizeRole(['HR', 'Admin']);
        $preview = $this->financialYearClosingService->preview($request->query('financial_year'));

        return view('Adminpanel.HRMS.FinancialYear.preview', $this->viewData(['preview' => $preview] + $preview));
    }

    public function close(Request $request): RedirectResponse
    {
        $actor = $this->authorizeRole(['Admin']);
        $financialYear = (string) $request->input('financial_year');

        try {
            if ($request->boolean('queue')) {
                CloseFinancialYearJob::dispatch($financialYear, (int) $actor->id, $request->ip());
                return redirect()->route('hrms.financial-year.index')->with('success', 'Financial Year closing queued.');
            }

            $closing = $this->financialYearClosingService->close($financialYear, $actor, $request->ip());
            return redirect()->route('hrms.financial-year.show', $closing->id)->with('success', 'Financial Year Closed');
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function history(Request $request): View
    {
        $this->authorizeRole(['Admin']);
        $history = $this->financialYearClosingService->history($request->query(), (int) $request->query('per_page', 25));

        return view('Adminpanel.HRMS.FinancialYear.history', $this->viewData(['history' => $history, 'filters' => $request->query()]));
    }

    public function show(int $closing): View
    {
        $this->authorizeRole(['Admin']);
        $details = $this->financialYearClosingService->show($closing);

        return view('Adminpanel.HRMS.FinancialYear.show', $this->viewData($details));
    }

    public function reopen(Request $request, int $closing): RedirectResponse
    {
        $actor = $this->authorizeRole(['Admin']);

        try {
            $this->financialYearClosingService->reopen($closing, $actor, $request->ip());
            return redirect()->route('hrms.financial-year.show', $closing)->with('success', 'Financial Year Reopened');
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    protected function viewData(array $data): array
    {
        return $data + [
            'perPageOptions' => [10, 25, 50, 100],
            'statusOptions' => ['closed' => 'Closed', 'reopened' => 'Reopened'],
        ];
    }

    protected function authorizeRole(array $roles): User
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $user->loadMissing('roles');
        if (! $user->roles->contains(fn ($role): bool => in_array($role->role_name, $roles, true))) {
            abort(403);
        }

        return $user;
    }
}
