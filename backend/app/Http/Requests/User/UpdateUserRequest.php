<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'unique:users,email,' . $this->route('user')],
            'password'  => ['sometimes', 'string', 'min:8', 'confirmed'],
            'role'      => ['sometimes', 'in:admin,user'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
