<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracker_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trackerId');  // Foreign key for tracker_schedule
            $table->unsignedBigInteger('employeeId'); // Foreign key for employees
            $table->boolean('respect');
            $table->boolean('uniforms');
            $table->boolean('commitmentToAttend');
            $table->boolean('performance');
            $table->float('finalResult');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('trackerId')->references('id')->on('tracker_schedule')->onDelete('cascade');
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_details');
    }
};