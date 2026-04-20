<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracker_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduleWeekId');  // Foreign Key for master_schedule
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('scheduleWeekId')->references('id')->on('master_schedule')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_schedule');
    }
};