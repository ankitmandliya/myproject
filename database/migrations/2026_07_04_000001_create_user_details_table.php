<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('emp_code', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->date('joining_date');
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->text('address')->nullable();
            $table->string('aadhaar', 20)->nullable()->unique();
            $table->string('pan', 20)->nullable()->unique();
            $table->string('profile_photo', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};


