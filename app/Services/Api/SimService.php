<?php
namespace App\Services\Api;

use App\Models\Sim;
use Exception;

class SimService
{
    // Create a new SIM card
    public function create(array $data)
    {
        try {
            return Sim::create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating SIM card: ' . $e->getMessage());
        }
    }

    // Update an existing SIM card
    public function update($id, array $data)
    {
        try {
            $sim = Sim::findOrFail($id);
            
            // Ensure the SimCardType isn't changed if it already exists
            if ($sim->SimCardType !== $data['SimCardType']) {
                $sim->SimCardType = $data['SimCardType'];
            }
            $sim->update($data);
            return $sim;
        } catch (Exception $e) {
            throw new Exception('Error updating SIM card: ' . $e->getMessage());
        }
    }

    // List all SIM cards sorted by ID
    public function index()
    {
        try {
            return Sim::orderBy('id')->get();
        } catch (Exception $e) {
            throw new Exception('Error fetching SIM cards: ' . $e->getMessage());
        }
    }

    // Soft delete a SIM card
    public function softDelete($id)
    {
        try {
            $sim = Sim::findOrFail($id);
            $sim->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting SIM card: ' . $e->getMessage());
        }
    }

    // Force delete a SIM card
    public function forceDelete($id)
    {
        try {
            $sim = Sim::withTrashed()->findOrFail($id);
            $sim->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Error force-deleting SIM card: ' . $e->getMessage());
        }
    }

    // Restore a soft-deleted SIM card
    public function restore($id)
    {
        try {
            $sim = Sim::withTrashed()->findOrFail($id);
            $sim->restore();
        } catch (Exception $e) {
            throw new Exception('Error restoring SIM card: ' . $e->getMessage());
        }
    }
}