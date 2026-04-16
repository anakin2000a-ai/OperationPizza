<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employeeId');  // Foreign Key for employees
            $table->unsignedBigInteger('scheduleWeekId');  // Foreign Key for master_schedule
            $table->float('totalHoursWorked');
            $table->float('trackerScore');
            $table->float('finalSalary');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('scheduleWeekId')->references('id')->on('master_schedule')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_card');
    }
};