<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employeetaxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employeeId')->nullable(); // Allow null for employeeId
            $table->unsignedBigInteger('taxesId')->nullable(); // Allow null for taxesId
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('editedBy')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('taxesId')->references('id')->on('taxes')->onDelete('set null');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employeetaxes');
    }
};