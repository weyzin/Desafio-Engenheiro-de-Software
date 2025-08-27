<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // policy cobre o resto
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => strtolower((string) $this->input('slug'))]);
        }
        if ($this->has('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }

    public function rules(): array
    {
        // cobre as duas formas de rota: /tenants/{id} OU /tenants/{tenant}
        $routeId = $this->route('id');
        $routeModel = $this->route('tenant'); // pode vir como Model (binding) ou string
        $id = is_object($routeModel) ? $routeModel->id : ($routeModel ?: $routeId);

        return [
            'name' => ['required','string','min:2','max:120'],
            'slug' => [
                'required','string','min:2','max:60',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                \Illuminate\Validation\Rule::unique('tenants','slug')->ignore($id,'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens (kebab-case).',
        ];
    }
}
