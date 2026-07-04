<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\StoreUserRequest;
use App\Http\Requests\HRMS\UpdateUserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for HRMS employee operations.
 */
class UserController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected UserServiceInterface $userService
    ) {
    }

    /** Display employee listing. */
    public function index(): View
    {
        $users = $this->userService->paginate();

        return view('Adminpanel.HRMS.Users.index', compact('users'));
    }

    /** Show employee creation form. */
    public function create(): View
    {
        return view('Adminpanel.HRMS.Users.create');
    }

    /** Store a new employee. */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->store($request->validated());

        return redirect()->route('hrms.users.index')->with('success', 'Employee created successfully.');
    }

    /** Display an employee. */
    public function show(int $id): View
    {
        $user = $this->userService->getById($id);

        return view('Adminpanel.HRMS.Users.show', compact('user'));
    }

    /** Show employee edit form. */
    public function edit(int $id): View
    {
        $user = $this->userService->getById($id);

        return view('Adminpanel.HRMS.Users.edit', compact('user'));
    }

    /** Update an employee. */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $this->userService->update($id, $request->validated());

        return redirect()->route('hrms.users.index')->with('success', 'Employee updated successfully.');
    }

    /** Delete an employee. */
    public function destroy(int $id): RedirectResponse
    {
        $this->userService->delete($id);

        return redirect()->route('hrms.users.index')->with('success', 'Employee deleted successfully.');
    }

    /** Assign a role to an employee. */
    public function assignRole(FormRequest $request): RedirectResponse
    {
        $this->userService->assignRole((int) $request->input('user_id'), (int) $request->input('role_id'));

        return redirect()->route('hrms.users.index')->with('success', 'Role assigned successfully.');
    }

    /** Remove an employee role. */
    public function removeRole(int $userId): RedirectResponse
    {
        $this->userService->removeRole($userId);

        return redirect()->route('hrms.users.index')->with('success', 'Role removed successfully.');
    }
}

