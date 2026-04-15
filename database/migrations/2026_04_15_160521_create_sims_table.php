<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->float('simCardInstallment')->default(8.25);
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('editedBy');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim');
    }
};