<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStoreAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $storeParam = $request->route('store');

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Senior manager can access all stores
        if ($user->role === 'SeniorManager') {
            return $next($request);
        }

        // Resolve store whether it comes as model or string
        $store = $storeParam instanceof Store
            ? $storeParam
            : Store::where('store', $storeParam)->first();

        if (!$store) {
            return response()->json([
                'message' => 'Store not found'
            ], 404);
        }

        if (in_array($user->role, [
            'SecondShiftStoreManager',
            'ThirdShiftStoreManager'
        ])) {
            if ((int) $user->store_id === (int) $store->id) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Unauthorized access to this store'
        ], 403);
    }
}