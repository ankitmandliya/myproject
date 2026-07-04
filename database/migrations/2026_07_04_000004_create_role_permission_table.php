<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('permission_name', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('role_id')->references('id')->on('role_master');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};
