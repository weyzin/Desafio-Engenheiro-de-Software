<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'superuser'; }
    public function rules(): array {
        return [
            'tenant_id' => ['nullable','uuid','exists:tenants,id','required_unless:role,superuser'],
            'name'      => ['required','string','max:120'],
            'email'     => ['required','email','max:190','unique:users,email'],
            'password'  => ['required','string','min:8'],
            'role'      => ['required','in:superuser,owner,agent'],
        ];
    }
}
