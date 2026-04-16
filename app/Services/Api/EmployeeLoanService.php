<?php

namespace App\Services\Api;

use App\Models\EmployeeLoan;
use App\Models\Loan;
use Exception;

class EmployeeLoanService
{
    // ✅ STORE
    public function create(array $data)
    {
        try {

            // ❌ Prevent multiple active loans for same employee
            $exists = EmployeeLoan::where('employeeId', $data['employeeId'])
                ->where('loanStatus', 'active')
                ->exists();

            if ($exists) {
                throw new Exception('This employee already has an active loan.');
            }
 
            $loan = Loan::findOrFail($data['loansId']);
 
            return EmployeeLoan::create([
                'employeeId'     => $data['employeeId'],
                'loansId'        => $data['loansId'],
                'loanStatus'     => 'active',
                'loanStartDate'  => now(),
                'loanRentAmount' => $loan->loanAmountWithTax,
                'createdBy'      => auth()->id(),
            ]);
 
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    // ✅ INDEX (sorted)
    public function getAll()
    {
        return EmployeeLoan::with(['employee', 'loan'])
            ->orderByDesc('loanRentAmount')
            ->get();
    }

    // ✅ SHOW (with relations)
    public function find($id)
    {
        return EmployeeLoan::with(['employee', 'loan'])
            ->findOrFail($id);
    }

  
    //soft delete
    public function softDelete($id, $reason)
    {
        $record = EmployeeLoan::findOrFail($id);

        if ($record->loanStatus === 'completed') {
            throw new Exception('Cannot delete completed loan.');
        }

        // ✅ assign values manually
        $record->deletedBy = auth()->id();
        $record->ReasonForDeletion = $reason;

        // ✅ save FIRST
        $record->save();

        // ✅ then soft delete
        $record->delete();
    }
}