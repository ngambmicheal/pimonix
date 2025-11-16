<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:' . $this->user()->id],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Please specify a receiver',
            'receiver_id.exists' => 'Receiver does not exist',
            'receiver_id.different' => 'You cannot send money to yourself',
            'amount.required' => 'Please specify an amount',
            'amount.min' => 'Amount must be at least 0.01',
            'amount.numeric' => 'Amount must be a valid number',
        ];
    }
}
