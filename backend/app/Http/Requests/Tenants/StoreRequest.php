<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'superuser'; }
    public function rules(): array {
        return [
            'name' => ['required','string','max:120'],
            'slug' => ['required','alpha_dash','max:60','unique:tenants,slug'],
        ];
    }
}
