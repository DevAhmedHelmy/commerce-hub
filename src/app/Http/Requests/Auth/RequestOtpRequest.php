<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Phone submission for an OTP request (FR-002). Rules only; Arabic messages come from
 * the translation catalog (R21/§39).
 */
class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
        ];
    }

    public function attributes(): array
    {
        return ['phone' => __('auth.fields.phone')];
    }
}
