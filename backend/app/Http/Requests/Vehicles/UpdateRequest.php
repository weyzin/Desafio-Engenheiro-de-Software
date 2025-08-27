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
            'brand'   => ['sometimes','string','max:80'],
            'model'   => ['sometimes','string','max:80'],
            'version' => ['sometimes','nullable','string','max:120'],
            'year'    => ['sometimes','integer','min:1900','max:2100'],
            'km'      => ['sometimes','nullable','integer','min:0'],
            'price'   => ['sometimes','numeric','min:0'],
            'status'  => ['sometimes', Rule::in(['available','reserved','sold'])],
            'notes'   => ['sometimes','nullable','string','max:1000'],
            'images'  => ['sometimes','array','max:10'],
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
