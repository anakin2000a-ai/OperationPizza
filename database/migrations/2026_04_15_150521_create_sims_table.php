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
        Schema::create('sims', function (Blueprint $table) {
              $table->id();
            $table->string('SimCardType')->unique();

            $table->float('simCardInstallment')->default(8.25);
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('editedBy')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('restrict');
     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sims');
    }
};
