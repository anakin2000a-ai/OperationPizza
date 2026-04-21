<?php

namespace App\Services\Api;

use App\Models\Deduction;
use App\Models\Sim;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SimService
{
    public function create(array $data): Sim
    {
        return DB::transaction(function () use ($data) {
            return Sim::create($data);
        });
    }

    public function update(int $id, array $data): Sim
    {
        return DB::transaction(function () use ($id, $data) {
            $sim = Sim::findOrFail($id);
            $sim->update($data);

            return $sim->fresh();
        });
    }

    public function index(int $perPage = 10)
    {
        return Sim::orderBy('id')->paginate($perPage);
    }

    public function softDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $sim = Sim::findOrFail($id);

            $isUsed = Deduction::where('SimId', $id)->exists();
            if ($isUsed) {
                throw new DomainException('Cannot delete SIM because it is assigned to an employee deduction.');
            }

            $sim->delete();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $sim = Sim::withTrashed()->findOrFail($id);

            $isUsed = Deduction::where('SimId', $id)->exists();
            if ($isUsed) {
                throw new DomainException('Cannot permanently delete SIM because it is assigned to an employee deduction.');
            }

            $sim->forceDelete();
        });
    }

    public function restore(int $id): Sim
    {
        return DB::transaction(function () use ($id) {
            $sim = Sim::withTrashed()->findOrFail($id);
            $sim->restore();

            return $sim->fresh();
        });
    }
}