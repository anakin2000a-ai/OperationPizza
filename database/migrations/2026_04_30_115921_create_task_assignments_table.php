<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');  // Employee who will perform the task
            $table->unsignedBigInteger('cleaning_task_id');  // Task assigned to the employee
            $table->unsignedBigInteger('store_id');  // Store where the task is being assigned
            $table->date('assigned_at');  // Date the task is assigned
            $table->enum('status', ['assigned', 'completed'])->default('assigned');  // Task status (assigned or completed)
            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('cleaning_task_id')->references('id')->on('cleaning_tasks')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_assignments');
    }
}