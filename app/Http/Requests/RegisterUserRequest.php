<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'avatar' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required' => 'Nickname is required.',
            'nickname.min' => 'Nickname must be at least 2 characters.',
            'nickname.max' => 'Nickname must not exceed 50 characters.',
            'nickname.regex' => 'Nickname may only contain letters, numbers, underscores and hyphens.',
            'avatar.required' => 'Avatar image is required.',
            'avatar.file' => 'Avatar must be a valid file.',
            'avatar.mimes' => 'Avatar must be an image (jpeg, jpg, png, gif, webp).',
            'avatar.max' => 'Avatar must not exceed 2MB.',
        ];
    }
}
