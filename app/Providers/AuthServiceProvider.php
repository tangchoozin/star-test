<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Transaction;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();

        // Combined policy for viewing transactions
        Gate::define('view-transaction', function ($user, $request) {
            $transactionId = $request->route('id');
            $transaction = Transaction::find($transactionId);

            if (!$transaction) {
                return false;
            }

            return $user->role === 'admin' || $transaction->user_id === $user->id;
        });

        // Only regular users can request transfers
        Gate::define('request-transfer', function ($user) {
            return $user->role === 'user';
        });
    }
}
