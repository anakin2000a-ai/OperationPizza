<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scorecardId');  // Foreign Key for score_card
            $table->float('loanAmount');
            $table->decimal('loanRentAmount', 10, 2)->nullable();

            $table->float('deductions');
            $table->string('deductionReason')->nullable();


            $table->float('finalSalary');
            // $table->boolean('IsapprovedByThirdShiftStoreManager')->default(false) ;
            // $table->unsignedBigInteger('approvedByThirdShiftStoreManagerId')->nullable();   
            // $table->boolean('IsapprovedBySeniorManager')->default(false) ;
            // $table->unsignedBigInteger('approvedBySeniorManagerId')->nullable();   

 
            $table->timestamp('paymentDate')->nullable();
            $table->enum('paymentStatus', ['paid', 'pending', 'overdue', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes

            // Foreign Key Constraints
            // $table->foreign('approvedByThirdShiftStoreManagerId')->references('id')->on('users')->onDelete('restrict');
            // $table->foreign('approvedBySeniorManagerId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('scorecardId')->references('id')->on('score_cards')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};