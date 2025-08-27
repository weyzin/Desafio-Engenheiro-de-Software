<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'superuser'; }
    public function rules(): array {
        $id = $this->route('id');
        return [
            'tenant_id' => ['nullable','uuid','exists:tenants,id','required_unless:role,superuser'],
            'name'      => ['required','string','max:120'],
            'email'     => ['required','email','max:190', Rule::unique('users','email')->ignore($id, 'id')],
            'password'  => ['nullable','string','min:8'],
            'role'      => ['required','in:superuser,owner,agent'],
        ];
    }
}
