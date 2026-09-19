<?php

declare(strict_types=1);

use App\Domain\Ordering\OrderService;

it('keeps order-item snapshots immutable when the catalog changes later', function () {
    ['customer' => $customer, 'input' => $input, 'product' => $product, 'sub' => $sub] = checkoutSetup(qty: 2, subStock: 100, subPrice: 50000);

    $order = app(OrderService::class)->place($customer, $input)->order;
    $item = $order->items()->first();

    expect($item->applied_unit_price)->toBe(50000)
        ->and($item->product_name)->toBe($product->localized('name'));

    // Change price + name after placement.
    $sub->update(['base_price' => 99000]);
    $product->update(['name_ar' => 'اسم مختلف تماماً']);

    $item->refresh();
    expect($item->applied_unit_price)->toBe(50000)
        ->and($item->line_total)->toBe(100000)
        ->and($item->product_name)->not->toBe('اسم مختلف تماماً');
});
