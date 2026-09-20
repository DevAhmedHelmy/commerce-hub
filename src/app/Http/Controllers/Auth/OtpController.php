<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\OtpService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Customer phone-OTP sign-in (US1, FR-001..FR-010). Orchestrates {@see OtpService};
 * no auth logic lives here. The active phone is carried in the session between steps.
 */
class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otp)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect('/home');
        }

        return view('auth.login');
    }

    public function request(RequestOtpRequest $request): RedirectResponse
    {
        $phone = OtpService::normalize($request->string('phone')->toString());

        $result = $this->otp->request($phone, $request->ip());

        $request->session()->put('otp_phone', $phone);

        if (! $result->sent) {
            return redirect()->route('otp.verify.show')
                ->withErrors(['phone' => __($result->messageKey ?? 'auth.otp.too_many_requests', [
                    'seconds' => $result->cooldownSeconds ?? 0,
                ])]);
        }

        return redirect()->route('otp.verify.show')->with('status', __('auth.otp.sent'));
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_phone')) {
            return redirect()->route('login');
        }

        return view('auth.verify', ['phone' => $request->session()->get('otp_phone')]);
    }

    public function verify(VerifyOtpRequest $request): RedirectResponse
    {
        $phone = $request->session()->get('otp_phone');

        if ($phone === null) {
            return redirect()->route('login');
        }

        $result = $this->otp->verify($phone, $request->string('code')->toString());

        if (! $result->ok) {
            return back()->withErrors(['code' => __($result->reason->messageKey())]);
        }

        // Deactivated customers cannot sign in until an admin reactivates them (prompt 48 §22).
        if (! $result->customer->isActive()) {
            $request->session()->forget('otp_phone');

            return redirect()->route('login')->withErrors(['phone' => __('auth.account_disabled')]);
        }

        Auth::guard('customer')->login($result->customer);
        $request->session()->regenerate();
        $request->session()->forget('otp_phone');

        return $result->needsOnboarding
            ? redirect()->route('onboarding.profile')
            : redirect('/home');
    }

    public function resend(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('otp_phone');

        if ($phone === null) {
            return redirect()->route('login');
        }

        $result = $this->otp->request($phone, $request->ip());

        if (! $result->sent) {
            return back()->withErrors(['code' => __($result->messageKey ?? 'auth.otp.too_many_requests', [
                'seconds' => $result->cooldownSeconds ?? 0,
            ])]);
        }

        return back()->with('status', __('auth.otp.resent'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
