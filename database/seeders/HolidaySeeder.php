<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('holidays')->insert([
            [
                'name' => 'New Year\'s Day',
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-01',
                'status' => 1,
            ],
            [
                'name' => 'Independence Day',
                'from_date' => '2026-07-04',
                'to_date' => '2026-07-04',
                'status' => 1,
            ],
            [
                'name' => 'Christmas Day',
                'from_date' => '2026-12-25',
                'to_date' => '2026-12-25',
                'status' => 1,
            ],
        ]); 
    }
}
