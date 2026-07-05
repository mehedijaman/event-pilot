<?php

namespace App\Http\Requests;

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
            'quantity' => ['required', 'integer', 'in:1,2,3,4,5,6'],
            'seat_position' => ['required', Rule::enum(SeatPosition::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'student_id_number' => [
                Rule::requiredIf(fn () => Package::find($this->package_id)?->requires_student_verification),
                'nullable', 'string', 'max:100',
            ],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'exists:payment_methods,slug,is_active,1'],
            'transaction_id' => [
                'required', 'string', 'max:255',
                Rule::unique('registrations')
                    ->where(fn ($q) => $q->whereNull('deleted_at'))
                    ->where('payment_method', $this->payment_method),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $package = Package::find($this->package_id);

            if (! $package) {
                return;
            }

            $expectedAmount = $package->price * $this->quantity;

            if ((float) $this->amount !== (float) $expectedAmount) {
                $validator->errors()->add(
                    'amount',
                    "Amount must be ৳{$expectedAmount} for {$this->quantity} ticket(s)."
                );
            }
        });
    }
}
