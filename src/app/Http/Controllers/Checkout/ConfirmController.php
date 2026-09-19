<?php

declare(strict_types=1);

namespace App\Http\Controllers\Checkout;

use App\Domain\Ordering\DTO\CheckoutInput;
use App\Domain\Ordering\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Order confirmation (US4, C13). Uses the one-time session submission token for duplicate-submit
 * protection and never reports false success — placement is transactional in {@see OrderService}.
 */
class ConfirmController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function confirm(Request $request): RedirectResponse
    {
        $session = $request->session()->get('checkout');
        $token = $request->session()->get('checkout.token');

        if ($session === null || $token === null) {
            return redirect()->route('checkout.delivery');
        }

        $input = new CheckoutInput(
            addressId: $session['address_id'],
            deliveryDate: $session['delivery_date'],
            slotId: $session['slot_id'],
            submissionToken: $token,
        );

        $result = $this->orders->place($request->user('customer'), $input);

        if (! $result->isPlaced()) {
            return redirect()->route('checkout.review')->with('status', __('checkout.terms_changed'));
        }

        // Clear checkout session so the token can't be replayed.
        $request->session()->forget(['checkout', 'checkout.token']);

        return redirect()->route('orders.success', $result->order);
    }
}
