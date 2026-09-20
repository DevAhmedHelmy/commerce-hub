<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * OTP code submission (FR-003). The active phone is held in the session by the flow.
 */
class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits_between:4,8'],
        ];
    }

    public function attributes(): array
    {
        return ['code' => __('auth.fields.code')];
    }
}
