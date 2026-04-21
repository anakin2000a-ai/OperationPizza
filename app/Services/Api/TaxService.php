<?php
namespace App\Services\Api;

use App\Models\Tax;
use Exception;

class TaxService
{
    // Create a new tax
    public function create(array $data)
    {
        try {
            $tax = Tax::create($data);
            return $tax;
        } catch (Exception $e) {
            throw new Exception('Error creating tax: ' . $e->getMessage());
        }
    }

    // Find a tax by ID
    public function find($id)
    {
        try {
            $tax = Tax::findOrFail($id);
            return $tax;
        } catch (Exception $e) {
            throw new Exception('Tax not found: ' . $e->getMessage());
        }
    }

    // Update a tax
    public function update($id, array $data)
    {
        try {
            $tax = Tax::findOrFail($id);
            $tax->update($data);
            return $tax;
        } catch (Exception $e) {
            throw new Exception('Error updating tax: ' . $e->getMessage());
        }
    }

    // Get all taxes sorted by created_at
    public function getAllSortedByCreatedAt()
    {
        try {
            return Tax::orderBy('created_at')->get();
        } catch (Exception $e) {
            throw new Exception('Error fetching taxes: ' . $e->getMessage());
        }
    }

    // Soft delete a tax
    public function softDelete($id)
    {
        try {
            $tax = Tax::findOrFail($id);
            $tax->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting tax: ' . $e->getMessage());
        }
    }

    // Restore a soft-deleted tax
    public function restore($id)
    {
        try {
            $tax = Tax::withTrashed()->findOrFail($id);
            $tax->restore();
            return $tax;
        } catch (Exception $e) {
            throw new Exception('Error restoring tax: ' . $e->getMessage());
        }
    }

    // Permanently delete a tax
    public function forceDelete($id)
    {
        try {
            $tax = Tax::withTrashed()->findOrFail($id);
            $tax->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Error permanently deleting tax: ' . $e->getMessage());
        }
    }
}