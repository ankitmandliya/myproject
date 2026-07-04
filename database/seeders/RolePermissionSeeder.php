<?php

namespace Database\Seeders;

use App\Models\RoleMaster;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $allPermissions = [
                'employee.view',
                'employee.create',
                'employee.edit',
                'employee.delete',
                'employee.view.self',
                'attendance.view',
                'attendance.manage',
                'attendance.view.self',
                'leave.view',
                'leave.manage',
                'leave.approve',
                'leave.reject',
                'leave.apply',
                'leave.view.self',
                'salary.view',
                'salary.generate',
                'salary.manage',
                'salary.view.self',
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'settings.view',
                'settings.manage',
            ];

            $permissionMap = [
                'Admin' => $allPermissions,
                'HR' => [
                    'employee.view',
                    'employee.create',
                    'employee.edit',
                    'attendance.view',
                    'attendance.manage',
                    'leave.view',
                    'leave.manage',
                    'leave.approve',
                    'leave.reject',
                    'salary.view',
                    'salary.generate',
                    'settings.view',
                ],
                'Manager' => [
                    'employee.view',
                    'attendance.view',
                    'leave.view',
                    'leave.approve',
                    'leave.reject',
                    'salary.view',
                ],
                'Employee' => [
                    'employee.view.self',
                    'attendance.view.self',
                    'leave.apply',
                    'leave.view.self',
                    'salary.view.self',
                ],
            ];

            foreach ($permissionMap as $roleName => $permissions) {
                $role = RoleMaster::where('role_name', $roleName)->first();

                if (! $role) {
                    throw new RuntimeException("Role [{$roleName}] must exist before seeding permissions.");
                }

                foreach ($permissions as $permissionName) {
                    RolePermission::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'permission_name' => $permissionName,
                        ],
                        [
                            'role_id' => $role->id,
                            'permission_name' => $permissionName,
                        ]
                    );
                }
            }
        });
    }
}
