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
            $table->enum('loanStatus', ['active', 'completed'])->default('active');
            $table->timestamp('loanStartDate');
            $table->decimal('loanRentAmount', 10, 2);

            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('editedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->string('ReasonForDeletion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employeeId')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('loansId')->references('id')->on('loans')->onDelete('restrict');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('editedBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('set null');

            $table->index('employeeId');
            $table->index('loansId');
            $table->index('loanStatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employeesloans');
    }
};