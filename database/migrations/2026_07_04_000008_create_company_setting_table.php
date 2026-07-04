<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_setting', function (Blueprint $table) {
            $table->id();
            $table->time('office_start_time')->default('10:00:00');
            $table->time('office_end_time')->default('18:00:00');
            $table->integer('late_after_minutes')->default(15);
            $table->integer('half_day_after_minutes')->default(120);
            $table->tinyInteger('salary_date')->default(5);
            $table->string('weekly_off', 50)->default('Sunday');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_setting');
    }
};
