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
      Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_id')
                ->constrained('payroll')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('role', [
                'third_shift',
                'senior'
            ]);

            $table->enum('status', [
                'approved',
                'rejected'
            ])->default('approved');

            $table->text('comment')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['payroll_id', 'role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};
