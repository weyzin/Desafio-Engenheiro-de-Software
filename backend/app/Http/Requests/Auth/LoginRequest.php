<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required','email','max:254'],
            'password' => ['required','string','min:6','max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'E-mail é obrigatório.',
            'email.email'    => 'E-mail inválido.',
            'password.required' => 'Senha é obrigatória.',
        ];
    }
}
