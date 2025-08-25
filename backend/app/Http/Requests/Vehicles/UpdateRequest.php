<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'brand'  => ['sometimes','string','max:80'],
            'model'  => ['sometimes','string','max:80'],
            'year'   => ['sometimes','integer','min:1900','max:2100'],
            'price'  => ['sometimes','numeric','min:0'],
            'status' => ['sometimes', Rule::in(['available','reserved','sold'])],
            'images' => ['sometimes','array','max:10'],
            'images.*' => ['string','url'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'O preço deve ser >= 0',
        ];
    }
}
