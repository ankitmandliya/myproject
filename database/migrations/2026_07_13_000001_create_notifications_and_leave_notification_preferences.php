<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->json('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table): void {
                if (! Schema::hasColumn('company_setting', 'enable_notifications')) {
                    $table->boolean('enable_notifications')->default(true);
                }
                if (! Schema::hasColumn('company_setting', 'enable_leave_reminders')) {
                    $table->boolean('enable_leave_reminders')->default(true);
                }
                if (! Schema::hasColumn('company_setting', 'enable_approval_reminders')) {
                    $table->boolean('enable_approval_reminders')->default(true);
                }
                if (! Schema::hasColumn('company_setting', 'enable_low_balance_alerts')) {
                    $table->boolean('enable_low_balance_alerts')->default(true);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_setting')) {
            Schema::table('company_setting', function (Blueprint $table): void {
                foreach (['enable_low_balance_alerts', 'enable_approval_reminders', 'enable_leave_reminders', 'enable_notifications'] as $column) {
                    if (Schema::hasColumn('company_setting', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('notifications');
    }
};

