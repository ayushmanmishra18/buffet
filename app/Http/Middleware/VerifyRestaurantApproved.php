<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RestaurantApplication;

class VerifyRestaurantApproved
{
    /**
     * Block restaurant owners whose application is still pending or rejected
     * from accessing the admin back-end.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Only applies to restaurant owner role (role id 3)
        if ($user->myrole !== 3) {
            return $next($request);
        }

        // Check if the user's account is inactive (status 10 = Status::INACTIVE)
        if (isset($user->status) && (int)$user->status === \App\Enums\Status::INACTIVE) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(route('login'))
                ->withErrors(['email' => 'Your account is pending admin approval. You will be notified once approved.']);
        }

        return $next($request);
    }
}
