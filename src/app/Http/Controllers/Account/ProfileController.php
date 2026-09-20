<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer Account (read-only). Displays the customer's persistent profile + default address.
 * Editing is ADMIN-ONLY (prompt 48 §21) — customers request changes through the admin.
 */
class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $customer = $request->user('customer');

        return view('account.index', [
            'customer' => $customer,
            'address' => $customer->defaultAddress()->with('deliveryArea')->first(),
        ]);
    }
}
