<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employeesloans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employeeId');
            $table->unsignedBigInteger('loansId');
            $table->enum('loanStatus', ['active', 'completed']);
            $table->timestamp('loanStartDate');
            $table->float('loanRentAmount');
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('editedBy');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('loansId')->references('id')->on('loans')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employeesloans');
    }
};