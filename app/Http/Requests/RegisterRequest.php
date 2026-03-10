<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // разрешаем всем
    }

    public function rules()
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string',  'min:8', 'confirmed'],

        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Введите имя пользователя',
            'username.unique' => 'Такой username уже существует',
            'email.required' => 'Введите email',
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Такой email уже зарегистрирован',
            'password.required' => 'Введите пароль',
            'password.min' => 'Пароль должен быть минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
