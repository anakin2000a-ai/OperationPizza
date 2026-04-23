<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CheckStoreManagerRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $store = $request->route('store'); // 👈 this is Store model

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only allow store managers
        if (in_array($user->role, ['SecondShiftStoreManager', 'ThirdShiftStoreManager'])) {

            // Compare IDs correctly
            if ($user->store_id == $store->id) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized access to this store'], 403);
    }
}