<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'brand'      => ['nullable','string','max:80'],
            'model'      => ['nullable','string','max:80'],
            'price_min'  => ['nullable','numeric','min:0'],
            'price_max'  => ['nullable','numeric','min:0'],
            'sort'       => ['nullable','string','max:100'], // ex: price,-year
            'page'       => ['nullable','integer','min:1'],
            'per_page'   => ['nullable','integer','min:1','max:100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $min = $this->input('price_min');
            $max = $this->input('price_max');
            if ($min !== null && $max !== null && $min > $max) {
                $v->errors()->add('price_min', 'Deve ser menor ou igual a price_max');
            }
        });
    }

    public function prepareForValidation(): void
    {
        // normaliza sort (tira espaços extras)
        if ($this->has('sort')) {
            $this->merge(['sort' => trim(preg_replace('/\s+/', '', (string)$this->input('sort')))]);
        }
    }
}
