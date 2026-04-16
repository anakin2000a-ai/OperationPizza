<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loanName')->nullable();

            $table->float('loanAmount');
            $table->float('taxValue')->nullable();
            $table->float('loanAmountWithTax');
            $table->enum('loanType', ['car', 'phone']);
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('editedBy')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};