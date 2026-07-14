<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_setting')) {
            return;
        }

        Schema::table('company_setting', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_setting', 'sandwich_leave_enabled')) {
                $table->boolean('sandwich_leave_enabled')->default(false)->comment('Enable sandwich leave calculation')->after('weekly_off');
            }
            if (! Schema::hasColumn('company_setting', 'holiday_between_leave_count')) {
                $table->boolean('holiday_between_leave_count')->default(true)->comment('Count holidays between leave dates')->after('sandwich_leave_enabled');
            }
            if (! Schema::hasColumn('company_setting', 'weekly_off_between_leave_count')) {
                $table->boolean('weekly_off_between_leave_count')->default(true)->comment('Count weekly offs between leave dates')->after('holiday_between_leave_count');
            }
            if (! Schema::hasColumn('company_setting', 'allow_half_day_leave')) {
                $table->boolean('allow_half_day_leave')->default(true)->comment('Allow half day leave applications')->after('weekly_off_between_leave_count');
            }
            if (! Schema::hasColumn('company_setting', 'leave_apply_before_days')) {
                $table->integer('leave_apply_before_days')->default(0)->comment('Minimum days before leave application')->after('allow_half_day_leave');
            }
            if (! Schema::hasColumn('company_setting', 'leave_cancel_before_days')) {
                $table->integer('leave_cancel_before_days')->default(0)->comment('Minimum days before leave cancellation')->after('leave_apply_before_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('company_setting')) {
            return;
        }

        Schema::table('company_setting', function (Blueprint $table): void {
            foreach ([
                'leave_cancel_before_days',
                'leave_apply_before_days',
                'allow_half_day_leave',
                'weekly_off_between_leave_count',
                'holiday_between_leave_count',
                'sandwich_leave_enabled',
            ] as $column) {
                if (Schema::hasColumn('company_setting', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
