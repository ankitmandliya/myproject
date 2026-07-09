<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Common\FileUploadServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\RoleMaster;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for HRMS employee management.
 */
class UserService implements UserServiceInterface
{
    /** Create a new user service instance. */
    public function __construct(
        protected User $user,
        protected UserRole $userRole,
        protected UserDetail $userDetail,
        protected RoleMaster $roleMaster,
        protected RolePermissionServiceInterface $rolePermissionService,
        protected CompanySettingServiceInterface $companySettingService,
        protected FileUploadServiceInterface $fileUploadService
    ) {
    }

    /** Get paginated users. */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->getAllUsers($perPage);
    }

    /** Get a user by ID. */
    public function getById(int $id): User
    {
        return $this->getUserById($id);
    }

    /** Store a user. */
    public function store(array $data): User
    {
        return $this->createUser($data);
    }

    /** Update a user. */
    public function update(int $id, array $data): User
    {
        return $this->updateUser($id, $data);
    }

    /** Delete a user. */
    public function delete(int $id): bool
    {
        return $this->deleteUser($id);
    }

    /** Return paginated users latest first with profile and roles loaded. */
    public function getAllUsers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->user
            ->with(['userDetail', 'roles'])
            ->latest()
            ->paginate($perPage);
    }

    /** Return filtered paginated users for employee listing. */
    public function getFilteredUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = $this->user->with(['userDetail', 'roles']);

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $query->where(function ($query) use ($name): void {
                $query->where('name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%")
                    ->orWhereHas('userDetail', function ($detailQuery) use ($name): void {
                        $detailQuery->where('first_name', 'like', "%{$name}%")
                            ->orWhere('last_name', 'like', "%{$name}%");
                    });
            });
        }

        $employeeCode = trim((string) ($filters['emp_code'] ?? ''));
        if ($employeeCode !== '') {
            $query->whereHas('userDetail', fn ($detailQuery) => $detailQuery->where('emp_code', 'like', "%{$employeeCode}%"));
        }

        $department = trim((string) ($filters['department'] ?? ''));
        if ($department !== '') {
            $query->whereHas('userDetail', fn ($detailQuery) => $detailQuery->where('department', 'like', "%{$department}%"));
        }

        $designation = trim((string) ($filters['designation'] ?? ''));
        if ($designation !== '') {
            $query->whereHas('userDetail', fn ($detailQuery) => $detailQuery->where('designation', 'like', "%{$designation}%"));
        }

        $role = trim((string) ($filters['role'] ?? ''));
        if ($role !== '') {
            $query->whereHas('roles', function ($roleQuery) use ($role): void {
                if (ctype_digit($role)) {
                    $roleQuery->where('role_master.id', (int) $role);

                    return;
                }

                $roleQuery->where('role_name', $role);
            });
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $query->whereHas('userDetail', fn ($detailQuery) => $detailQuery->where('status', (bool) $filters['status']));
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }
    /** Return a complete employee profile by user ID. */
    public function getUserById(int $id): User
    {
        $this->validatePositiveId($id, 'User ID');

        $user = $this->user->with(['userDetail', 'roles'])->find($id);

        if (! $user instanceof User) {
            throw new RuntimeException("User [{$id}] was not found.");
        }

        return $user;
    }

    /** Create an employee user and profile. */
    public function createUser(array $data): User
    {
        $this->validateRequired($data, 'name', 'Name');
        $this->validateRequired($data, 'email', 'Email');
        $this->validateRequired($data, 'password', 'Password');
        $this->validateRequired($data, 'emp_code', 'Employee code');
        $this->validateRequired($data, 'first_name', 'First name');

        if ($this->emailExists((string) $data['email'])) {
            throw new RuntimeException('Email already exists.');
        }

        if ($this->employeeCodeExists((string) $data['emp_code'])) {
            throw new RuntimeException('Employee code already exists.');
        }

        $data = $this->prepareProfilePhotoUpload($data);

        return DB::transaction(function () use ($data): User {
            $user = $this->user->create($this->userPayload($data, true));

            $user->userDetail()->create($this->userDetailPayload($data));

            $roleIds = $this->extractRoleIds($data);

            if ($roleIds !== []) {
                $this->syncRoles($user->id, $roleIds);
            }

            return $this->getUserById($user->id);
        });
    }

    /** Update an employee user and profile. */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);

        if (isset($data['email']) && $this->emailExists((string) $data['email'], $id)) {
            throw new RuntimeException('Email already exists.');
        }

        if (isset($data['emp_code']) && $this->employeeCodeExists((string) $data['emp_code'], $user->userDetail?->id)) {
            throw new RuntimeException('Employee code already exists.');
        }

        $oldProfilePhoto = (string) ($user->userDetail?->profile_photo ?? '');
        $data = $this->prepareProfilePhotoUpload($data);
        $newProfilePhoto = $data['profile_photo'] ?? null;

        $updatedUser = DB::transaction(function () use ($user, $data): User {
            $userPayload = $this->userPayload($data, false);

            if ($userPayload !== []) {
                $user->update($userPayload);
            }

            $detailPayload = $this->userDetailPayload($data, false);

            if ($detailPayload !== []) {
                $user->userDetail()->updateOrCreate(
                    ['user_id' => $user->id],
                    $detailPayload
                );
            }

            if (array_key_exists('role_id', $data) || array_key_exists('role_ids', $data) || array_key_exists('roles', $data)) {
                $this->syncRoles($user->id, $this->extractRoleIds($data));
            }

            return $this->getUserById($user->id);
        });

        if (is_string($newProfilePhoto)) {
            $this->deleteReplacedProfilePhoto($oldProfilePhoto, $newProfilePhoto);
        }

        return $updatedUser;
    }

    /** Delete an employee user, profile, and role assignments. */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);

        return DB::transaction(function () use ($user): bool {
            $user->userDetail?->delete();
            $user->roles()->detach();

            return (bool) $user->delete();
        });
    }

    /** Activate an employee. */
    public function activateUser(int $id): bool
    {
        return $this->updateStatus($id, true);
    }

    /** Deactivate an employee. */
    public function deactivateUser(int $id): bool
    {
        return $this->updateStatus($id, false);
    }

    /** Assign a role to a user without duplicates. */
    public function assignRole(int $userId, int $roleId): bool
    {
        $user = $this->getUserById($userId);
        $this->findRole($roleId);

        return DB::transaction(function () use ($user, $roleId): bool {
            if (! $user->roles()->where('role_master.id', $roleId)->exists()) {
                $user->roles()->attach($roleId);
            }

            return true;
        });
    }

    /** Remove an assigned role from a user. */
    public function removeRole(int $userId, ?int $roleId = null): bool
    {
        $user = $this->getUserById($userId);

        return DB::transaction(function () use ($user, $roleId): bool {
            if ($roleId === null) {
                $user->roles()->detach();

                return true;
            }

            $this->findRole($roleId);
            $user->roles()->detach($roleId);

            return true;
        });
    }

    /** Replace existing user roles. */
    public function syncRoles(int $userId, array $roles): bool
    {
        $user = $this->getUserById($userId);
        $roleIds = $this->normalizeRoleIds($roles);

        foreach ($roleIds as $roleId) {
            $this->findRole($roleId);
        }

        return DB::transaction(function () use ($user, $roleIds): bool {
            $user->roles()->sync($roleIds);

            return true;
        });
    }

    /** Search employees by name, email, and employee code. */
    public function searchUsers(string $keyword): Collection
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            throw new InvalidArgumentException('Search keyword is required.');
        }

        return $this->user
            ->with(['userDetail', 'roles'])
            ->where('email', 'like', "%{$keyword}%")
            ->orWhereHas('userDetail', function ($query) use ($keyword): void {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('emp_code', 'like', "%{$keyword}%");
            })
            ->latest()
            ->get();
    }

    /** Get active employees. */
    public function getActiveUsers(): Collection
    {
        return $this->user
            ->with(['userDetail', 'roles'])
            ->whereHas('userDetail', fn ($query) => $query->active())
            ->latest()
            ->get();
    }

    /** Get inactive employees. */
    public function getInactiveUsers(): Collection
    {
        return $this->user
            ->with(['userDetail', 'roles'])
            ->whereHas('userDetail', fn ($query) => $query->where('status', 0))
            ->latest()
            ->get();
    }

    /** Get employees by role name. */
    public function getEmployeesByRole(string $role): Collection
    {
        $role = trim($role);

        if ($role === '') {
            throw new InvalidArgumentException('Role is required.');
        }

        if (! $this->rolePermissionService->roleExists($role)) {
            throw new RuntimeException("Role [{$role}] does not exist.");
        }

        return $this->user
            ->with(['userDetail', 'roles'])
            ->whereHas('roles', fn ($query) => $query->where('role_name', $role))
            ->latest()
            ->get();
    }

    /** Determine whether a user exists. */
    public function userExists(int $id): bool
    {
        $this->validatePositiveId($id, 'User ID');

        return $this->user->whereKey($id)->exists();
    }

    /** Determine whether an email exists. */
    public function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $email = trim($email);

        if ($email === '') {
            throw new InvalidArgumentException('Email is required.');
        }

        $query = $this->user->where('email', $email);

        if ($ignoreUserId !== null) {
            $query->whereKeyNot($ignoreUserId);
        }

        return $query->exists();
    }

    /** Determine whether an employee code exists. */
    public function employeeCodeExists(string $empCode, ?int $ignoreUserDetailId = null): bool
    {
        $empCode = trim($empCode);

        if ($empCode === '') {
            throw new InvalidArgumentException('Employee code is required.');
        }

        $query = $this->userDetail->where('emp_code', $empCode);

        if ($ignoreUserDetailId !== null) {
            $query->whereKeyNot($ignoreUserDetailId);
        }

        return $query->exists();
    }

    /** Return complete employee profile. */
    public function getEmployeeProfile(int $userId): User
    {
        return $this->getUserById($userId);
    }

    /** Upload an employee profile photo into the public upload directory. */
    protected function prepareProfilePhotoUpload(array $data): array
    {
        if (($data['profile_photo'] ?? null) instanceof UploadedFile) {
            $data['profile_photo'] = $this->fileUploadService->uploadImage(
                $data['profile_photo'],
                'uploads/employees',
                'public_path'
            );
        }

        return $data;
    }

    /** Delete a replaced employee photo when it belongs to the public employee upload directory. */
    protected function deleteReplacedProfilePhoto(string $oldPath, string $newPath): void
    {
        $oldPath = trim($oldPath);

        if ($oldPath === '' || $oldPath === $newPath || ! str_starts_with($oldPath, 'uploads/employees/')) {
            return;
        }

        $this->fileUploadService->deleteFile($oldPath, 'public_path');
    }

    /** Update active status on the employee profile. */
    protected function updateStatus(int $id, bool $status): bool
    {
        $user = $this->getUserById($id);

        if (! $user->userDetail instanceof UserDetail) {
            throw new RuntimeException("User [{$id}] does not have an employee profile.");
        }

        return (bool) $user->userDetail->update(['status' => $status]);
    }

    /** Build user payload from request data. */
    protected function userPayload(array $data, bool $creating): array
    {
        $payload = [];

        foreach (['name', 'email', 'phone', 'otp'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if ($creating || ! empty($data['password'])) {
            $payload['password'] = Hash::make((string) $data['password']);
        }

        return $payload;
    }

    /** Build user detail payload from request data. */
    protected function userDetailPayload(array $data, bool $includeDefaults = true): array
    {
        $fields = [
            'emp_code',
            'first_name',
            'last_name',
            'gender',
            'dob',
            'joining_date',
            'department',
            'designation',
            'basic_salary',
            'address',
            'aadhaar',
            'pan',
            'profile_photo',
            'status',
        ];

        $payload = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if ($includeDefaults && ! array_key_exists('status', $payload)) {
            $payload['status'] = true;
        }

        return $payload;
    }

    /** Extract role IDs from supported payload keys. */
    protected function extractRoleIds(array $data): array
    {
        if (array_key_exists('role_ids', $data)) {
            return $this->normalizeRoleIds((array) $data['role_ids']);
        }

        if (array_key_exists('roles', $data)) {
            return $this->normalizeRoleIds((array) $data['roles']);
        }

        if (array_key_exists('role_id', $data) && $data['role_id'] !== null && $data['role_id'] !== '') {
            return $this->normalizeRoleIds([$data['role_id']]);
        }

        return [];
    }

    /** Normalize role IDs into unique positive integers. */
    protected function normalizeRoleIds(array $roles): array
    {
        return collect($roles)
            ->map(fn (mixed $roleId): int => (int) $roleId)
            ->filter(fn (int $roleId): bool => $roleId > 0)
            ->unique()
            ->values()
            ->all();
    }

    /** Find a role by ID. */
    protected function findRole(int $roleId): RoleMaster
    {
        $this->validatePositiveId($roleId, 'Role ID');

        $role = $this->roleMaster->find($roleId);

        if (! $role instanceof RoleMaster) {
            throw new RuntimeException("Role [{$roleId}] does not exist.");
        }

        return $role;
    }

    /** Validate a positive integer ID. */
    protected function validatePositiveId(int $id, string $label): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("{$label} must be a positive integer.");
        }
    }

    /** Validate a required data field. */
    protected function validateRequired(array $data, string $field, string $label): void
    {
        if (! array_key_exists($field, $data) || trim((string) $data[$field]) === '') {
            throw new InvalidArgumentException("{$label} is required.");
        }
    }
}

