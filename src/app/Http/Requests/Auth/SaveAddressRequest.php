<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Default delivery address (FR-007/FR-008, C6). `delivery_area_id` is optional in the
 * auth phase (delivery areas are configured in Phase G); area eligibility is enforced by
 * the Delivery domain once areas exist.
 */
class SaveAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('customer') !== null;
    }

    public function rules(): array
    {
        return [
            'delivery_area_id' => ['nullable', 'integer'],
            'address_line' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'address_line' => __('auth.fields.address_line'),
            'building' => __('auth.fields.building'),
            'floor' => __('auth.fields.floor'),
            'unit' => __('auth.fields.unit'),
            'landmark' => __('auth.fields.landmark'),
            'delivery_notes' => __('auth.fields.delivery_notes'),
        ];
    }
}
