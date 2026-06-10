<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopViewPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'limit' => ['nullable','integer']
        ];
    }
}
