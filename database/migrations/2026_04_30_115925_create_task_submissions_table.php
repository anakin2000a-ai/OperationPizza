<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_id');  // Task assignment ID
            $table->string('image_path');  // Path to the submitted image
            $table->timestamps();

            // Foreign key
            $table->foreign('task_assignment_id')->references('id')->on('task_assignments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_submissions');
    }
}