<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('leave_types', 'carry_forward_limit')) {
                $table->decimal('carry_forward_limit', 8, 2)->default(0)->after('carry_forward_enabled');
            }
        });

        if (! Schema::hasTable('financial_year_closings')) {
            Schema::create('financial_year_closings', function (Blueprint $table): void {
                $table->id();
                $table->string('financial_year', 9)->unique();
                $table->string('next_financial_year', 9);
                $table->string('status', 20)->default('open');
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('reopened_by')->nullable();
                $table->timestamp('reopened_at')->nullable();
                $table->unsignedInteger('employees_processed')->default(0);
                $table->unsignedInteger('employees_skipped')->default(0);
                $table->unsignedInteger('inactive_employees')->default(0);
                $table->unsignedInteger('carry_forward_count')->default(0);
                $table->unsignedInteger('reset_count')->default(0);
                $table->unsignedInteger('error_count')->default(0);
                $table->unsignedInteger('execution_time_ms')->default(0);
                $table->string('ip_address', 45)->nullable();
                $table->json('summary')->nullable();
                $table->json('execution_log')->nullable();
                $table->json('audit_timeline')->nullable();
                $table->timestamps();

                $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('reopened_by')->references('id')->on('users')->nullOnDelete();
                $table->index(['financial_year', 'status']);
            });
        }

        if (! Schema::hasTable('financial_year_archives')) {
            Schema::create('financial_year_archives', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('financial_year_closing_id');
                $table->string('financial_year', 9);
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->decimal('opening_balance', 8, 2)->default(0);
                $table->decimal('allocated', 8, 2)->default(0);
                $table->decimal('consumed', 8, 2)->default(0);
                $table->decimal('remaining', 8, 2)->default(0);
                $table->decimal('carry_forward', 8, 2)->default(0);
                $table->decimal('closing_balance', 8, 2)->default(0);
                $table->timestamp('generated_at');
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamps();

                $table->foreign('financial_year_closing_id')->references('id')->on('financial_year_closings')->cascadeOnDelete();
                $table->foreign('employee_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete();
                $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
                $table->unique(['financial_year_closing_id', 'employee_id', 'leave_type_id'], 'fy_archive_unique');
                $table->index(['financial_year', 'employee_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_year_archives');
        Schema::dropIfExists('financial_year_closings');

        Schema::table('leave_types', function (Blueprint $table): void {
            if (Schema::hasColumn('leave_types', 'carry_forward_limit')) {
                $table->dropColumn('carry_forward_limit');
            }
        });
    }
};
