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
            'brand'   => ['required','string','max:80'],
            'model'   => ['required','string','max:80'],
            'version' => ['nullable','string','max:120'],
            'year'    => ['required','integer','min:1900','max:2100'],
            'km'      => ['nullable','integer','min:0'],
            'price'   => ['required','numeric','min:0'],
            'status'  => ['nullable', Rule::in(['available','reserved','sold'])],
            'notes'   => ['nullable','string','max:1000'],
            'images'  => ['nullable','array','max:10'],
            'images.*'=> ['string','url'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'O preço deve ser maior ou igual a 0.',
            'km.min'    => 'A quilometragem (km) deve ser maior ou igual a 0.',
        ];
    }
}
