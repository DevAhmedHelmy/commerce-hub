<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Domain\Customers\CustomerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteProfileRequest;
use App\Http\Requests\Auth\SaveAddressRequest;
use App\Models\DeliveryArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer Account (prompt 44 §20): view + edit the persistent business/contact profile and the
 * default delivery address. This — not checkout — is where canonical customer data is edited.
 */
class ProfileController extends Controller
{
    public function __construct(private readonly CustomerService $customers)
    {
    }

    public function show(Request $request): View
    {
        $customer = $request->user('customer');

        return view('account.index', [
            'customer' => $customer,
            'address' => $customer->defaultAddress()->with('deliveryArea')->first(),
        ]);
    }

    public function editProfile(Request $request): View
    {
        return view('account.profile', ['customer' => $request->user('customer')]);
    }

    public function updateProfile(CompleteProfileRequest $request): RedirectResponse
    {
        $this->customers->completeOnboarding($request->user('customer'), $request->validated());

        return redirect()->route('account.index')->with('status', __('account.saved'));
    }

    public function editAddress(Request $request): View
    {
        return view('account.address', [
            'customer' => $request->user('customer'),
            'address' => $request->user('customer')->defaultAddress()->first(),
            'areas' => DeliveryArea::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function updateAddress(SaveAddressRequest $request): RedirectResponse
    {
        $this->customers->updateDefaultAddress($request->user('customer'), $request->validated());

        return redirect()->route('account.index')->with('status', __('account.saved'));
    }
}
