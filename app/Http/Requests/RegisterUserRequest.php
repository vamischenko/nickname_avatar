<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на регистрацию пользователя.
 *
 * Валидирует поля nickname и avatar перед передачей в контроллер.
 *
 * Правила для nickname: обязательное, строка, 2–50 символов, только буквы/цифры/_ /-
 * Правила для avatar: обязательное, файл, MIME jpeg/jpg/png/gif/webp, максимум 2 МБ
 */
class RegisterUserRequest extends FormRequest
{
    /**
     * Все запросы к этому эндпоинту разрешены без дополнительной авторизации.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации полей запроса.
     *
     * @return array<string, array<int, string>> Карта поле => список правил
     */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'avatar' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ];
    }

    /**
     * Возвращает кастомные сообщения об ошибках валидации.
     *
     * @return array<string, string> Карта правило.поле => сообщение
     */
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
