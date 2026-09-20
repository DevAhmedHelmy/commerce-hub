<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a customer who was deactivated mid-session (prompt 48 §22): logs them out and returns them
 * to sign-in with a clear Arabic message. Their data/orders are preserved; only access is revoked.
 */
class EnsureCustomerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer !== null && ! $customer->isActive()) {
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['phone' => __('auth.account_disabled')]);
        }

        return $next($request);
    }
}
