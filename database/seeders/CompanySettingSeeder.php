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
            CompanySetting::firstOrCreate(
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
        });
    }
}
