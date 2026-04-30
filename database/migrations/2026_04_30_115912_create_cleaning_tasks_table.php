<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCleaningTasksTable extends Migration
{
    public function up()
    {
        Schema::create('cleaning_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Task name (e.g., VCM Cleaning)
            $table->enum('frequency', ['daily', 'weekly', 'monthly']); // Frequency type
            $table->integer('times_per_frequency')->default(1); // Number of times the task should occur per frequency
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cleaning_tasks');
    }
}