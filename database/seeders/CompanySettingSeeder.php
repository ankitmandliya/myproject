<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $settings = CompanySetting::firstOrCreate(
                ['id' => 1],
                [
                    'office_start_time' => '10:00:00',
                    'office_end_time' => '18:00:00',
                    'late_after_minutes' => 15,
                    'half_day_after_minutes' => 120,
                    'salary_date' => 5,
                    'weekly_off' => 'Sunday',
                ]
            );

            $settings->update([
                'sandwich_leave_enabled' => false,
                'holiday_between_leave_count' => true,
                'weekly_off_between_leave_count' => true,
                'allow_half_day_leave' => true,
                'leave_apply_before_days' => 0,
                'leave_cancel_before_days' => 0,
                'leave_auto_approval' => false,
                'leave_approval_levels' => ['manager', 'hr', 'admin'],
            ]);
        });
    }
}

