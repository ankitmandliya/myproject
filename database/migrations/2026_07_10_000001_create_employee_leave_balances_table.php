<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('leave_types', 'annual_allocation')) {
                $table->decimal('annual_allocation', 8, 2)->default(0)->after('total_days');
            }
            if (! Schema::hasColumn('leave_types', 'monthly_allocation')) {
                $table->decimal('monthly_allocation', 8, 2)->default(0)->after('annual_allocation');
            }
            if (! Schema::hasColumn('leave_types', 'carry_forward_enabled')) {
                $table->boolean('carry_forward_enabled')->default(false)->after('monthly_allocation');
            }
            if (! Schema::hasColumn('leave_types', 'sandwich_applicable')) {
                $table->boolean('sandwich_applicable')->default(false)->after('carry_forward_enabled');
            }
            if (! Schema::hasColumn('leave_types', 'half_day_allowed')) {
                $table->boolean('half_day_allowed')->default(false)->after('sandwich_applicable');
            }
            if (! Schema::hasColumn('leave_types', 'requires_approval')) {
                $table->boolean('requires_approval')->default(true)->after('half_day_allowed');
            }
        });

        if (! Schema::hasTable('employee_leave_balances')) {
            Schema::create('employee_leave_balances', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->string('financial_year', 9);
                $table->decimal('allocated', 8, 2)->default(0);
                $table->decimal('used', 8, 2)->default(0);
                $table->decimal('remaining', 8, 2)->default(0);
                $table->decimal('carry_forward', 8, 2)->default(0);
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete();
                $table->unique(['employee_id', 'leave_type_id', 'financial_year'], 'employee_leave_balance_unique');
                $table->index(['employee_id', 'financial_year']);
                $table->index(['leave_type_id', 'financial_year']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');

        Schema::table('leave_types', function (Blueprint $table): void {
            foreach (['requires_approval', 'half_day_allowed', 'sandwich_applicable', 'carry_forward_enabled', 'monthly_allocation', 'annual_allocation'] as $column) {
                if (Schema::hasColumn('leave_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};