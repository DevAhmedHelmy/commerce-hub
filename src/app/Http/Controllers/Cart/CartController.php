<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Domain\Cart\CartService;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer cart (US3, C10). Thin orchestration over {@see CartService}; all pricing/availability
 * is recomputed server-side. The cart never reserves stock and never shows delivery estimates.
 */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(Request $request): View
    {
        return view('cart.index', [
            'cart' => $this->cart->view($request->user('customer')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $unit = ProductUnit::query()->findOrFail($data['product_unit_id']);
        $this->cart->add($request->user('customer'), $unit, (int) ($data['quantity'] ?? 1));

        return redirect()->route('cart.index')->with('status', __('cart.added'));
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $this->cart->updateQuantity($item, (int) $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $this->cart->remove($item);

        return redirect()->route('cart.index');
    }

    /** Ensure the line belongs to the authenticated customer's cart. */
    private function authorizeItem(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->customer_id === $request->user('customer')->id, 403);
    }
}
