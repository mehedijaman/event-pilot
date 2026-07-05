<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\SeatPosition;
use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'exists:packages,id,is_active,1'],
            'seat_position' => ['required', Rule::enum(SeatPosition::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'student_id_number' => [
                Rule::requiredIf(fn () => Package::find($this->package_id)?->requires_student_verification),
                'nullable', 'string', 'max:100',
            ],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'transaction_id' => [
                'required', 'string', 'max:255',
                Rule::unique('registrations')
                    ->where(fn ($q) => $q->whereNull('deleted_at'))
                    ->where('payment_method', $this->payment_method),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
