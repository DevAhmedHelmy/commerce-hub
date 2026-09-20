<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ordering gate (FR-009): only an authenticated customer who has completed onboarding
 * may reach ordering routes. Others are sent to sign-in or to resume onboarding.
 */
class EnsureOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer === null) {
            return redirect()->route('login');
        }

        if (! $customer->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.profile');
        }

        return $next($request);
    }
}
