<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * First-time profile (FR-006/FR-007, C5). B2B identity only — no KYC/tax fields.
 */
class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('customer') !== null;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'contact_person_name' => ['required', 'string', 'max:255'],
            'whatsapp_phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'business_name' => __('auth.fields.business_name'),
            'contact_person_name' => __('auth.fields.contact_person_name'),
            'whatsapp_phone' => __('auth.fields.whatsapp_phone'),
        ];
    }
}
