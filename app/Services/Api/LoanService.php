<?php

namespace App\Services\Api;

use App\Models\EmployeeLoan;
use App\Models\Loan;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function create(array $data): Loan
    {
        return DB::transaction(function () use ($data) {
            $data['loanAmountWithTax'] = $this->calculateLoanAmountWithTax(
                $data['loanAmount'],
                $data['taxValue']
            );

            $data['createdBy'] = auth()->id();

            return Loan::create($data);
        });
    }

    public function find(int $id): Loan
    {
        return Loan::findOrFail($id);
    }

    public function update(int $id, array $data): Loan
    {
        return DB::transaction(function () use ($id, $data) {
            $loan = Loan::findOrFail($id);

            $loanAmount = $data['loanAmount'] ?? $loan->loanAmount;
            $taxValue = $data['taxValue'] ?? $loan->taxValue;

            $data['loanAmountWithTax'] = $this->calculateLoanAmountWithTax(
                $loanAmount,
                $taxValue
            );

            $data['editedBy'] = auth()->id();

            $loan->update($data);

            return $loan->fresh();
        });
    }

    public function getAllSortedByLoanAmountWithTax(int $perPage = 10)
    {
        return Loan::orderByDesc('loanAmountWithTax')->paginate($perPage);
    }

    public function softDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $loan = Loan::findOrFail($id);

            $isUsed = EmployeeLoan::withTrashed()
                ->where('loansId', $id)
                ->exists();

            if ($isUsed) {
                throw new DomainException('Cannot delete loan because it is assigned to employees.');
            }

            $loan->delete();
        });
    }

    public function restore(int $id): Loan
    {
        return DB::transaction(function () use ($id) {
            $loan = Loan::withTrashed()->findOrFail($id);

            if (!$loan->trashed()) {
                throw new DomainException('Loan is not deleted.');
            }

            $loan->restore();

            return $loan->fresh();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $loan = Loan::withTrashed()->findOrFail($id);

            $isUsed = EmployeeLoan::withTrashed()
                ->where('loansId', $id)
                ->exists();

            if ($isUsed) {
                throw new DomainException('Cannot permanently delete loan because it is assigned to employees.');
            }

            $loan->forceDelete();
        });
    }

    private function calculateLoanAmountWithTax(float|int $loanAmount, float|int $taxValue): float
    {
        return (float) ($loanAmount + ($loanAmount * ($taxValue / 100)));
    }
}