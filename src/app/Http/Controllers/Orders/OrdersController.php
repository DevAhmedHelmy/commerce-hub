<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Domain\Ordering\OrderService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer order history + details + self-cancel (US9, C13/C14/C15). A customer only ever sees
 * their own orders; snapshots render the placement-time record (immutable).
 */
class OrdersController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->forCustomer($request->user('customer')->id)
            ->latest()
            ->paginate(15);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('orders.show', [
            'order' => $order->load('items'),
            'canCancel' => $this->orders->canCustomerCancel($order),
        ]);
    }

    public function success(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('orders.success', ['order' => $order->load('items')]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($request, $order);
        abort_unless($this->orders->canCustomerCancel($order), 403);

        $this->orders->cancel($order, 'customer', $request->input('reason'));

        return redirect()->route('orders.show', $order)->with('status', __('orders.cancelled'));
    }

    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->customer_id === $request->user('customer')->id, 404);
    }
}
