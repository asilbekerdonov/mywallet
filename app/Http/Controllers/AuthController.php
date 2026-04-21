<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\LoginRequest;
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    // Показывает страницу входа
    public function login(): View
    {
        return view('auth.login');
    }

    // Обрабатывает форму входа
    public function loginsystem(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if ($this->authService->login($credentials, $request)) {
            return redirect()->intended('/dashboards/');
        }

        return redirect()->route('login')
            ->withErrors(['username' => 'Неверный логин или пароль.'])
            ->withInput();
    }

    // Показывает страницу регистрации
    public function register(): View
    {
        return view('auth.register');
    }

    // Обрабатывает форму регистрации
    public function registersystem(RegisterRequest $request): RedirectResponse
    {
        $this->authService->register($request->validated(), $request);

        return redirect('/dashboards/');
    }

    // Выход
    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}