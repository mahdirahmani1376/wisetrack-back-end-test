<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property $title
 * @property $image
 * @property $content
 */
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required','string'],
            'image' => ['file'],
            'content' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
