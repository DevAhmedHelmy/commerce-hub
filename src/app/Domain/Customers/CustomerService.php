<?php

declare(strict_types=1);

namespace App\Domain\Customers;

use App\Domain\Auth\OtpService;
use App\Models\Customer;
use App\Models\CustomerAddress;

/**
 * Customer profile + default-address lifecycle (FR-006..FR-009, C5/C6). Onboarding is
 * complete only once the profile (business + contact) AND a default address exist; that
 * gate controls ordering (FR-009).
 */
final class CustomerService
{
    public function findOrCreateByPhone(string $phone): Customer
    {
        return Customer::firstOrCreate(['phone' => OtpService::normalize($phone)]);
    }

    /**
     * @param  array{business_name: string, contact_person_name: string, whatsapp_phone?: ?string}  $profile
     */
    public function completeOnboarding(Customer $customer, array $profile): Customer
    {
        $customer->fill([
            'business_name' => $profile['business_name'],
            'contact_person_name' => $profile['contact_person_name'],
            'whatsapp_phone' => $profile['whatsapp_phone'] ?? null,
        ])->save();

        $this->markOnboardedIfReady($customer);

        return $customer;
    }

    public function setDefaultAddress(Customer $customer, array $data): CustomerAddress
    {
        $payload = [
            'delivery_area_id' => $data['delivery_area_id'] ?? null,
            'address_line' => $data['address_line'],
            'building' => $data['building'] ?? null,
            'floor' => $data['floor'] ?? null,
            'unit' => $data['unit'] ?? null,
            'landmark' => $data['landmark'] ?? null,
            'delivery_notes' => $data['delivery_notes'] ?? null,
            'is_default' => true,
        ];

        $address = $customer->defaultAddress()->first();

        if ($address !== null) {
            $address->update($payload);
        } else {
            $address = $customer->addresses()->create($payload);
        }

        $this->markOnboardedIfReady($customer);

        return $address;
    }

    public function updateDefaultAddress(Customer $customer, array $data): CustomerAddress
    {
        return $this->setDefaultAddress($customer, $data);
    }

    public function canPlaceOrders(Customer $customer): bool
    {
        return $customer->hasCompletedOnboarding();
    }

    /** Mark onboarding complete once profile + default address are both present (FR-009). */
    private function markOnboardedIfReady(Customer $customer): void
    {
        if ($customer->hasCompletedOnboarding()) {
            return;
        }

        $hasProfile = filled($customer->business_name) && filled($customer->contact_person_name);
        $hasAddress = $customer->defaultAddress()->exists();

        if ($hasProfile && $hasAddress) {
            $customer->forceFill(['onboarding_completed_at' => now()])->save();
        }
    }
}
