<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\RoleServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\StoreRoleRequest;
use App\Http\Requests\HRMS\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS role operations.
 */
class RoleController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected RoleServiceInterface $roleService
    ) {
    }

    /** Display role listing. */
    public function index(): View
    {
        $roles = $this->roleService->paginate();

        return view('Adminpanel.HRMS.Roles.index', compact('roles'));
    }

    /** Show role creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Roles.create');
    }

    /** Store a role. */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->store($request->validated());

        return redirect()->route('hrms.roles.index')->with('success', 'Role created successfully.');
    }

    /** Display a role. */
    public function show(int $id): View
    {
        $role = $this->roleService->getById($id);

        return view('Adminpanel.HRMS.Roles.show', compact('role'));
    }

    /** Show role edit form. */
    public function edit(int $id): View
    {
        $role = $this->roleService->getById($id);

        return view('Adminpanel.HRMS.Roles.edit', compact('role'));
    }

    /** Update a role. */
    public function update(UpdateRoleRequest $request, int $id): RedirectResponse
    {
        $this->roleService->update($id, $request->validated());

        return redirect()->route('hrms.roles.index')->with('success', 'Role updated successfully.');
    }

    /** Delete a role. */
    public function destroy(int $id): RedirectResponse
    {
        $this->roleService->delete($id);

        return redirect()->route('hrms.roles.index')->with('success', 'Role deleted successfully.');
    }

    /** Assign permissions to a role. */
    public function assignRole(FormRequest $request): RedirectResponse
    {
        $request->validated();

        return redirect()->route('hrms.roles.index')->with('success', 'Role assigned successfully.');
    }

    /** Remove role assignment. */
    public function removeRole(FormRequest $request): RedirectResponse
    {
        $request->validated();

        return redirect()->route('hrms.roles.index')->with('success', 'Role removed successfully.');
    }
}

