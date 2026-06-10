<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property $name
 * @property $email
 * @property $password
 */
class RegisterUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
