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
        Schema::create('shift_requirement_days', function (Blueprint $table) {
        $table->id();

        $table->foreignId('store_id')
            ->constrained('stores')
            ->cascadeOnDelete();

        $table->enum('day_of_week', [
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ]);

        $table->timestamps();

        $table->unique(['store_id', 'day_of_week']); // 🔥 prevent duplicates
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_requirement_days');
    }
};
