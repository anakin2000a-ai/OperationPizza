<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('employeeId');  // Foreign Key for employees
            $table->unsignedBigInteger('scorecardId');  // Foreign Key for score_card
            $table->float('loanAmount');
            $table->float('deductions');
            $table->float('taxes');
            $table->float('finalSalary');
            $table->boolean('IsapprovedByThirdShiftStoreManager');
             $table->unsignedBigInteger('approvedByThirdShiftStoreManagerId');   
             $table->boolean('IsapprovedBySeniorManager');
            $table->unsignedBigInteger('approvedBySeniorManagerId');   

 
            $table->timestamp('paymentDate')->nullable();
            $table->enum('paymentStatus', ['paid', 'pending', 'overdue', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('approvedByThirdShiftStoreManagerId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approvedBySeniorManagerId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('scorecardId')->references('id')->on('score_cards')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};