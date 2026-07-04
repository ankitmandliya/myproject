<?php

namespace Database\Seeders;

use App\Models\RoleMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $roles = [
                'Admin' => 'Full system access',
                'HR' => 'Human resources management access',
                'Manager' => 'Team management access',
                'Employee' => 'Employee self-service access',
            ];

            foreach ($roles as $roleName => $description) {
                RoleMaster::updateOrCreate(
                    ['role_name' => $roleName],
                    [
                        'description' => $description,
                        'status' => 1,
                    ]
                );
            }
        });
    }
}
