<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('leave_types')->insert([
            [
                'leave_name' => 'Casual Leave',
                'leave_code' => 'CL',
                'total_days' => 12,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Sick Leave',
                'leave_code' => 'SL',
                'total_days' => 12,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Earned Leave',
                'leave_code' => 'EL',
                'total_days' => 18,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Compensatory Off',
                'leave_code' => 'CO',
                'total_days' => 0,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Maternity Leave',
                'leave_code' => 'ML',
                'total_days' => 180,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Paternity Leave',
                'leave_code' => 'PTL',
                'total_days' => 15,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Marriage Leave',
                'leave_code' => 'MRL',
                'total_days' => 5,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Bereavement Leave',
                'leave_code' => 'BL',
                'total_days' => 3,
                'is_paid' => true,
                'status' => true,
            ],
            [
                'leave_name' => 'Leave Without Pay',
                'leave_code' => 'LWP',
                'total_days' => 0,
                'is_paid' => false,
                'status' => true,
            ],
        ]);
    }
}