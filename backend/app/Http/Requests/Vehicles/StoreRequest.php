<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'brand'  => ['required','string','max:80'],
            'model'  => ['required','string','max:80'],
            'year'   => ['required','integer','min:1900','max:2100'],
            'price'  => ['required','numeric','min:0'],
            'status' => ['nullable', Rule::in(['available','reserved','sold'])],
            'images' => ['nullable','array','max:10'],
            'images.*' => ['string','url'], // URLs (públicas/assinadas)
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'O preço deve ser >= 0',
        ];
    }
}
