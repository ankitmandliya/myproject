<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_apply')) {
            return;
        }

        Schema::table('leave_apply', function (Blueprint $table): void {
            if (! Schema::hasColumn('leave_apply', 'requested_days')) {
                $table->decimal('requested_days', 5, 2)->default(0)->after('total_days');
            }
            if (! Schema::hasColumn('leave_apply', 'holiday_days')) {
                $table->decimal('holiday_days', 5, 2)->default(0)->after('requested_days');
            }
            if (! Schema::hasColumn('leave_apply', 'weekly_off_days')) {
                $table->decimal('weekly_off_days', 5, 2)->default(0)->after('holiday_days');
            }
            if (! Schema::hasColumn('leave_apply', 'sandwich_days')) {
                $table->decimal('sandwich_days', 5, 2)->default(0)->after('weekly_off_days');
            }
            if (! Schema::hasColumn('leave_apply', 'payable_leave_days')) {
                $table->decimal('payable_leave_days', 5, 2)->default(0)->after('sandwich_days');
            }
            if (! Schema::hasColumn('leave_apply', 'leave_calculation_json')) {
                $table->json('leave_calculation_json')->nullable()->after('payable_leave_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leave_apply')) {
            return;
        }

        Schema::table('leave_apply', function (Blueprint $table): void {
            foreach ([
                'leave_calculation_json',
                'payable_leave_days',
                'sandwich_days',
                'weekly_off_days',
                'holiday_days',
                'requested_days',
            ] as $column) {
                if (Schema::hasColumn('leave_apply', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
