<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_requirement_times', function (Blueprint $table) {
        $table->id();

        $table->foreignId('shift_requirement_day_id')
            ->constrained('shift_requirement_days')
            ->cascadeOnDelete();

        $table->time('start_time');
        $table->time('end_time');

        $table->foreignId('skill_id')
            ->constrained('skills')
            ->restrictOnDelete();

        $table->unsignedInteger('required_employees')->default(1);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_requirement_times');
    }
};
