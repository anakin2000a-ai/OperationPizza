<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employeeId');
            $table->unsignedBigInteger('ApartmentId')->nullable(); // Foreign key for apartments
            $table->unsignedBigInteger('SimId')->nullable(); // Foreign key for sim
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('editedBy')->nullable();
            $table->timestamps();
 
            // Foreign Key Constraints
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('ApartmentId')->references('id')->on('apartments')->onDelete('set null');
            $table->foreign('SimId')->references('id')->on('sims')->onDelete('set null');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};