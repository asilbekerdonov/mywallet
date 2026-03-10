<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'amount' => 'required|integer|min:500',

        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Введите сумму',
            'amount.integer' => 'Введите только цифры',
            'amount.min' => 'Минимальная сумма для перевода — не меньше 500',
        ];
    }
}
