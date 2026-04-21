<?php

 namespace App\Services\Api;

use App\Models\Apartment;
use Exception;
class ApartmentService
{
    // Create a new apartment
    public function create(array $data)
    {
        try {
            $apartment = Apartment::create($data);
            return $apartment;
        } catch (Exception $e) {
            throw new Exception('Error creating apartment: ' . $e->getMessage());
        }
    }

    // Find an apartment by ID
    public function find($id)
    {
        try {
            $apartment = Apartment::findOrFail($id);
            return $apartment;
        } catch (Exception $e) {
            throw new Exception('Apartment not found: ' . $e->getMessage());
        }
    }

    // Update an apartment
    public function update($id, array $data)
    {
        try {
            $apartment = Apartment::findOrFail($id);
            $apartment->update($data);
            return $apartment;
        } catch (Exception $e) {
            throw new Exception('Error updating apartment: ' . $e->getMessage());
        }
    }

    // Soft delete an apartment
    public function softDelete($id)
    {
        try {
            $apartment = Apartment::findOrFail($id);
            $apartment->delete(); // Soft delete
        } catch (Exception $e) {
            throw new Exception('Error deleting apartment: ' . $e->getMessage());
        }
    }

    // Restore a soft-deleted apartment
    public function restore($id)
    {
        try {
            $apartment = Apartment::withTrashed()->findOrFail($id);
            $apartment->restore(); // Restore
            return $apartment;
        } catch (Exception $e) {
            throw new Exception('Error restoring apartment: ' . $e->getMessage());
        }
    }

    // Permanently delete an apartment
    public function forceDelete($id)
    {
        try {
            $apartment = Apartment::withTrashed()->findOrFail($id);
            $apartment->forceDelete(); // Permanently delete
        } catch (Exception $e) {
            throw new Exception('Error permanently deleting apartment: ' . $e->getMessage());
        }
    }

    // Get all apartments, sorted by ID
    public function getAllSortedById()
    {
        try {
            return Apartment::orderBy('id')->get(); // Retrieve all apartments sorted by ID
        } catch (Exception $e) {
            throw new Exception('Error fetching apartments: ' . $e->getMessage());
        }
    }
}