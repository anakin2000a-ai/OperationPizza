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

        // Ensure the user is a SecondShiftStoreManager or ThirdShiftStoreManager
        if ($user->role === 'SecondShiftStoreManager' || $user->role === 'ThirdShiftStoreManager') {
            // Check if the store in the route matches the user's store (you may need to adjust this based on your store relation)
            // if ($user->store_id == $request->route('store')) {
                return $next($request);
            // }
        }

        return response()->json(['error' => 'Unauthorized access to this store'], 403);
    }
}