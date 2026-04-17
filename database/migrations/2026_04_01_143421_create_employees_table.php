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
        Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->foreignId('store_id')->constrained()->restrictOnDelete();

        $table->string('FirstName');
        $table->string('LastName');
        $table->boolean('HaveCar');
        $table->string('phone');
        $table->string('email');
        $table->date('hire_date');

        $table->enum('status', ['termination', 'resignation', 'hired','OJE']);

        $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
