<?php

namespace App\Services\Api;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\Loan;
use DomainException;
use Illuminate\Support\Facades\DB;
   use Carbon\Carbon;
class EmployeeLoanService
{
    public function create(array $data): EmployeeLoan
    {
        return DB::transaction(function () use ($data) {

            $employee = Employee::findOrFail($data['employeeId']);

            // Prevent multiple active loans
            $exists = EmployeeLoan::where('employeeId', $employee->id)
                ->where('loanStatus', 'active')
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new DomainException('This employee already has an active loan.');
            }

            $loan = Loan::findOrFail($data['loansId']);

            return EmployeeLoan::create([
                'employeeId'     => $employee->id,
                'loansId'        => $loan->id,
                'loanStatus'     => 'active',
                'loanStartDate'  => now(),
                'loanRentAmount' => $loan->loanAmountWithTax,
                'createdBy'      => auth()->id(),
            ]);
        });
    }

    public function getAll(int $perPage = 10)
    {
        return EmployeeLoan::with(['employee', 'loan'])
            ->orderByDesc('loanRentAmount')
            ->paginate($perPage);
    }

    public function find(int $id): EmployeeLoan
    {
        return EmployeeLoan::with(['employee', 'loan'])
            ->findOrFail($id);
    }



    public function softDelete(int $id, string $reason): void
    {
        DB::transaction(function () use ($id, $reason) {

            $record = EmployeeLoan::findOrFail($id);

            // ❌ Cannot delete completed loan
            if ($record->loanStatus === 'completed') {
                throw new DomainException('Cannot delete completed loan.');
            }

            // ❌ Allow delete ONLY on same day
            $createdDate = Carbon::parse($record->created_at)->toDateString();
            $today = now()->toDateString();

            if ($createdDate !== $today) {
                throw new DomainException('You can only delete the loan on the same day it was created.');
            }

            // ✅ proceed with delete
            $record->deletedBy = auth()->id();
            $record->ReasonForDeletion = $reason;
            $record->save();

            $record->delete();
        });
    }
}