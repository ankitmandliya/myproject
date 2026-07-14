<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_apply')) {
            try {
                DB::statement("ALTER TABLE leave_apply MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Pending'");
            } catch (Throwable $exception) {
                // SQLite and some test drivers do not support MODIFY; new columns still apply.
            }

            Schema::table('leave_apply', function (Blueprint $table): void {
                if (! Schema::hasColumn('leave_apply', 'approval_level')) {
                    $table->string('approval_level', 50)->nullable()->after('status');
                }
                if (! Schema::hasColumn('leave_apply', 'manager_id')) {
                    $table->foreignId('manager_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'manager_status')) {
                    $table->string('manager_status', 50)->nullable()->after('manager_id');
                }
                if (! Schema::hasColumn('leave_apply', 'manager_remarks')) {
                    $table->text('manager_remarks')->nullable()->after('manager_status');
                }
                if (! Schema::hasColumn('leave_apply', 'manager_action_at')) {
                    $table->timestamp('manager_action_at')->nullable()->after('manager_remarks');
                }
                if (! Schema::hasColumn('leave_apply', 'hr_id')) {
                    $table->foreignId('hr_id')->nullable()->after('manager_action_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'hr_status')) {
                    $table->string('hr_status', 50)->nullable()->after('hr_id');
                }
                if (! Schema::hasColumn('leave_apply', 'hr_remarks')) {
                    $table->text('hr_remarks')->nullable()->after('hr_status');
                }
                if (! Schema::hasColumn('leave_apply', 'hr_action_at')) {
                    $table->timestamp('hr_action_at')->nullable()->after('hr_remarks');
                }
                if (! Schema::hasColumn('leave_apply', 'admin_id')) {
                    $table->foreignId('admin_id')->nullable()->after('hr_action_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'admin_status')) {
                    $table->string('admin_status', 50)->nullable()->after('admin_id');
                }
                if (! Schema::hasColumn('leave_apply', 'admin_remarks')) {
                    $table->text('admin_remarks')->nullable()->after('admin_status');
                }
                if (! Schema::hasColumn('leave_apply', 'admin_action_at')) {
                    $table->timestamp('admin_action_at')->nullable()->after('admin_remarks');
                }
                if (! Schema::hasColumn('leave_apply', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('admin_action_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
                if (! Schema::hasColumn('leave_apply', 'cancelled_by')) {
                    $table->foreignId('cancelled_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
                }
                if (! Schema::hasColumn('leave_apply', 'revoked_by')) {
                    $table->foreignId('revoked_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leave_apply', 'revoked_at')) {
                    $table->timestamp('revoked_at')->nullable()->after('revoked_by');
                }
                if (! Schema::hasColumn('leave_apply', 'approval_timeline')) {
                    $table->json('approval_timeline')->nullable()->after('revoked_at');
                }
                if (! Schema::hasColumn('leave_apply', 'approval_audit_log')) {
                    $table->json('approval_audit_log')->nullable()->after('approval_timeline');
                }
                if (! Schema::hasColumn('leave_apply', 'attendance_warning')) {
                    $table->text('attendance_warning')->nullable()->after('approval_audit_log');
                }
                if (! Schema::hasColumn('leave_apply', 'payroll_locked_at')) {
                    $table->timestamp('payroll_locked_at')->nullable()->after('attendance_warning');
                }
            });
        }

        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table): void {
                if (! Schema::hasColumn('user_details', 'reporting_manager_id')) {
                    $table->foreignId('reporting_manager_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table): void {
                if (! Schema::hasColumn('company_setting', 'leave_auto_approval')) {
                    $table->boolean('leave_auto_approval')->default(false)->after('leave_cancel_before_days');
                }
                if (! Schema::hasColumn('company_setting', 'leave_approval_levels')) {
                    $table->json('leave_approval_levels')->nullable()->after('leave_auto_approval');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table): void {
                foreach (['leave_approval_levels', 'leave_auto_approval'] as $column) {
                    if (Schema::hasColumn('company_setting', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('user_details') && Schema::hasColumn('user_details', 'reporting_manager_id')) {
            Schema::table('user_details', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('reporting_manager_id');
            });
        }

        if (Schema::hasTable('leave_apply')) {
            Schema::table('leave_apply', function (Blueprint $table): void {
                foreach (['manager_id', 'hr_id', 'admin_id', 'rejected_by', 'cancelled_by', 'revoked_by'] as $column) {
                    if (Schema::hasColumn('leave_apply', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }

                foreach ([
                    'approval_level', 'manager_status', 'manager_remarks', 'manager_action_at',
                    'hr_status', 'hr_remarks', 'hr_action_at', 'admin_status', 'admin_remarks',
                    'admin_action_at', 'rejected_at', 'cancelled_at', 'revoked_at', 'approval_timeline',
                    'approval_audit_log', 'attendance_warning', 'payroll_locked_at',
                ] as $column) {
                    if (Schema::hasColumn('leave_apply', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
