<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use App\Models\Transaction;
use App\Models\User;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = auth()->user();

        // Special case for viewing transactions
        if ($permission === 'view-transaction') {
            $transactionId = $request->route('id');
            $transaction = Transaction::find($transactionId);

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Admin can view all, users can only view their own
            if (!($user->role === 'admin' || $transaction->transfer->user_id === $user->id)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return $next($request);
        }

        // Check other permissions using Gate
        if (Gate::denies($permission, $request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
