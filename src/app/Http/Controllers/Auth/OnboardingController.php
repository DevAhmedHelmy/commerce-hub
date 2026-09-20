<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Customers\CustomerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteProfileRequest;
use App\Http\Requests\Auth\SaveAddressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * First-time onboarding (US1, FR-006..FR-009): profile (C04) then default address (C05).
 * Returning, already-onboarded customers never reach these screens (they are redirected
 * straight into the app after verifying).
 */
class OnboardingController extends Controller
{
    public function __construct(private readonly CustomerService $customers)
    {
    }

    public function showProfile(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($customer->hasCompletedOnboarding()) {
            return redirect('/home');
        }

        return view('auth.profile', ['customer' => $customer]);
    }

    public function saveProfile(CompleteProfileRequest $request): RedirectResponse
    {
        $this->customers->completeOnboarding(
            Auth::guard('customer')->user(),
            $request->validated(),
        );

        return redirect()->route('onboarding.address');
    }

    public function showAddress(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($customer->hasCompletedOnboarding()) {
            return redirect('/home');
        }

        return view('auth.address', [
            'customer' => $customer,
            'address' => $customer->defaultAddress()->first(),
            'areas' => \App\Models\DeliveryArea::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function saveAddress(SaveAddressRequest $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $this->customers->setDefaultAddress($customer, $request->validated());

        return redirect('/home')->with('status', __('auth.onboarding.complete'));
    }
}
