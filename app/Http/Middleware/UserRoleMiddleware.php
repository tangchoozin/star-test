<?php

namespace App\Http\Middleware;

use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
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
        } elseif ($permission == 'request-transfer') {
            if ($user->role === 'user' || $user->role === 'admin') {
                //Need to confirm whether admin also can do request transfer. If yes, then need to add $user->role === 'admin' here
                return $next($request);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        // Check other permissions using Gate
        if (Gate::denies($permission, $request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
