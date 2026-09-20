<?php

declare(strict_types=1);

namespace App\Http\Controllers\Checkout;

use App\Domain\Cart\CartService;
use App\Domain\Delivery\DeliveryService;
use App\Domain\Ordering\DTO\CheckoutInput;
use App\Domain\Ordering\OrderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Two-step checkout (US4, C11/C12). Step 1 collects delivery choices; step 2 shows the
 * server-recomputed review. Everything commercial is revalidated by {@see OrderService}.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CartService $cart,
        private readonly DeliveryService $delivery,
    ) {
    }

    public function delivery(Request $request): View|RedirectResponse
    {
        $customer = $request->user('customer');
        if ($this->cart->view($customer)->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $defaultDate = Carbon::now()->addDay()->startOfDay();

        return view('checkout.delivery', [
            'addresses' => $customer->addresses()->get(),
            'defaultDate' => $defaultDate->toDateString(),
            'slots' => $this->delivery->availableSlots($defaultDate),
        ]);
    }

    public function deliveryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'slot_id' => ['required', 'integer'],
        ]);

        abort_unless(
            $request->user('customer')->addresses()->whereKey($data['address_id'])->exists(),
            403,
        );

        $request->session()->put('checkout', [
            'address_id' => (int) $data['address_id'],
            'delivery_date' => $data['delivery_date'],
            'slot_id' => (int) $data['slot_id'],
        ]);

        return redirect()->route('checkout.review');
    }

    public function review(Request $request): View|RedirectResponse
    {
        $session = $request->session()->get('checkout');
        if ($session === null) {
            return redirect()->route('checkout.delivery');
        }

        $customer = $request->user('customer');
        $input = $this->input($session);
        $review = $this->orders->review($customer, $input);

        // Acknowledge current prices so the confirm step can detect changes that happen afterwards.
        $this->cart->acknowledgePrices($customer);

        // One-time submission token for duplicate-submit protection.
        $token = $request->session()->get('checkout.token');
        if ($token === null) {
            $token = (string) \Illuminate\Support\Str::uuid();
            $request->session()->put('checkout.token', $token);
        }

        return view('checkout.review', ['review' => $review, 'token' => $token]);
    }

    /** @param array{address_id:int,delivery_date:string,slot_id:int} $session */
    private function input(array $session, ?string $token = null): CheckoutInput
    {
        return new CheckoutInput(
            addressId: $session['address_id'],
            deliveryDate: $session['delivery_date'],
            slotId: $session['slot_id'],
            submissionToken: $token,
        );
    }
}
