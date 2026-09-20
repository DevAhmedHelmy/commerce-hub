<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create an admin user with the given RBAC role(s), ensuring the roles/permissions exist first.
 * Idempotent seeding keeps this cheap under RefreshDatabase.
 */
function adminWithRole(string ...$roles): App\Models\User
{
    (new Database\Seeders\RolesAndPermissionsSeeder())->run();

    $user = App\Models\User::factory()->create();
    $user->assignRole($roles);

    // Ensure the freshly assigned roles/permissions are visible within the same request.
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/** A full-access super_admin admin user. */
function superAdmin(): App\Models\User
{
    return adminWithRole(Database\Seeders\RolesAndPermissionsSeeder::ROLE_SUPER_ADMIN);
}

/** A single sellable product unit with the given base price (minor units) for pricing tests. */
function pricingUnit(int $baseMinor): App\Models\ProductUnit
{
    $product = App\Models\Product::factory()->create();

    return App\Models\ProductUnit::factory()->create([
        'product_id' => $product->id,
        'base_price' => $baseMinor,
    ]);
}

/**
 * Build a placeable checkout scenario: onboarded customer, active area + matching future slot,
 * default address, and one in-stock sub-unit line already in the cart. Returns the pieces + a
 * ready CheckoutInput.
 *
 * @return array{customer: App\Models\Customer, input: App\Domain\Ordering\DTO\CheckoutInput, product: App\Models\Product, sub: App\Models\ProductUnit}
 */
function checkoutSetup(int $qty = 1, int $subStock = 100, int $subPrice = 50000): array
{
    $customer = App\Models\Customer::factory()->onboarded()->create();
    $area = App\Models\DeliveryArea::factory()->create(['base_fee' => 3000]);
    $date = Illuminate\Support\Carbon::now()->addDay()->startOfDay();
    $slot = App\Models\DeliverySlot::factory()->create(['day_of_week' => $date->isoWeekday()]);
    $address = App\Models\CustomerAddress::create([
        'customer_id' => $customer->id, 'delivery_area_id' => $area->id,
        'is_default' => true, 'address_line' => 'شارع ١',
    ]);
    $product = App\Models\Product::factory()->withUnits(conversion: 12, subStock: $subStock, subPrice: $subPrice)->create();
    $sub = $product->subUnit()->first();
    app(App\Domain\Cart\CartService::class)->add($customer, $sub, $qty);

    $input = new App\Domain\Ordering\DTO\CheckoutInput(
        addressId: $address->id,
        deliveryDate: $date->toDateString(),
        slotId: $slot->id,
        submissionToken: (string) Illuminate\Support\Str::uuid(),
    );

    return compact('customer', 'input', 'product', 'sub');
}
