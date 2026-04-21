<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        private readonly CardService $cardService
    ) {}

    public function register(array $data, Request $request): User
    {
        $user = User::create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'card'     => $this->cardService->generateCardNumber(),
            'password' => bcrypt($data['password']),
            'balance'  => 1_000_000,
        ]);

        auth()->login($user);
        $request->session()->regenerate();

        return $user;
    }

    public function login(array $credentials, Request $request): bool
    {
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return true;
        }

        return false;
    }
}