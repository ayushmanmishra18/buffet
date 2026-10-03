<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class VerifyRestaurantOwnerSetup
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $excludedRouteNames = [
            'admin.restaurants.create',
            'admin.restaurants.store',
            'admin.restaurants.edit',
            'admin.restaurants.update',
            'admin.restaurants.index',
            'admin.restaurant.restaurant-store',
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;

        if ($routeName && in_array($routeName, $excludedRouteNames, true)) {
            return $next($request);
        }
        if ($user->myrole == UserRole::RESTAURANTOWNER) {
            $restaurant = $user->restaurant ?? null;

            if (! $restaurant || ! ($restaurant->id ?? null)) {
                return redirect()->route('admin.restaurants.index')->withError('Please complete the restaurant information first');
            }
        }

        return $next($request);
    }
}
