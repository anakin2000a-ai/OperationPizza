<?php
namespace App\Services\Api;

use App\Models\Loan;
use Exception;

class LoanService
{
    // Create a new loan
    public function create(array $data)
    {
        try {
            // Calculate loanAmountWithTax
            $loanAmountWithTax = $data['loanAmount'] + ($data['loanAmount'] * ($data['taxValue'] / 100));

            // Add loanAmountWithTax to the data array
            $data['loanAmountWithTax'] = $loanAmountWithTax;

            // Create the loan using the data passed from the controller
            $loan = Loan::create($data);
            return $loan;
        } catch (Exception $e) {
            throw new Exception('Error creating loan: ' . $e->getMessage());
        }
    }

    // Find a loan by ID
    public function find($id)
    {
        try {
            return Loan::findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('Loan not found: ' . $e->getMessage());
        }
    }

    // Update a loan
    public function update($id, array $data)
    {
        try {
            $loan = Loan::findOrFail($id);
            $loan->update($data);
            $loan->loanAmountWithTax = $loan->loanAmount + ($loan->loanAmount * ($loan->taxValue / 100));  // Recalculate loanAmountWithTax
            $loan->save();
            return $loan;
        } catch (Exception $e) {
            throw new Exception('Error updating loan: ' . $e->getMessage());
        }
    }

    // Get all loans sorted by loanAmountWithTax
    public function getAllSortedByLoanAmountWithTax()
    {
        try {
            return Loan::orderByDesc('loanAmountWithTax')->get();
        } catch (Exception $e) {
            throw new Exception('Error fetching loans: ' . $e->getMessage());
        }
    }

    // Soft delete a loan
    public function softDelete($id)
    {
        try {
            $loan = Loan::findOrFail($id);
            $loan->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting loan: ' . $e->getMessage());
        }
    }

    // Restore a soft-deleted loan
    public function restore($id)
    {
        try {
            $loan = Loan::withTrashed()->findOrFail($id);
            $loan->restore();
            return $loan;
        } catch (Exception $e) {
            throw new Exception('Error restoring loan: ' . $e->getMessage());
        }
    }

    // Permanently delete a loan
    public function forceDelete($id)
    {
        try {
            $loan = Loan::withTrashed()->findOrFail($id);
            $loan->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Error permanently deleting loan: ' . $e->getMessage());
        }
    }
}