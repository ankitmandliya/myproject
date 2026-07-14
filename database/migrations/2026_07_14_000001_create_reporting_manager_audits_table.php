<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reporting_manager_audits')) {
            return;
        }

        Schema::create('reporting_manager_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('old_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['employee_id', 'changed_at']);
            $table->index(['new_manager_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_manager_audits');
    }
};
