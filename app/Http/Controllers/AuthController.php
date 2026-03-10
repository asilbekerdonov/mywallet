<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function loginsystem(Request $request)
    {

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboards/');
        } else {

            return "<center><h1>Username or password is wrong!</h1> <hr> <a href='/'>Back to Authintication </a></center>";
        }

    }

    // Generating card
    public function generateCardNumber()
    {
        // Первые 4 цифры фиксированы
        $prefix = '8600';

        // Генерируем оставшиеся 12 цифр
        $randomDigits = '';
        for ($i = 0; $i < 12; $i++) {
            $randomDigits .= mt_rand(0, 9);
        }

        // Форматируем как 8600_XXXX_XXXX_XXXX
        return $prefix.' '.
            substr($randomDigits, 0, 4).' '.
            substr($randomDigits, 4, 4).' '.
            substr($randomDigits, 8, 4);
    }

    public function registersystem(RegisterRequest $request)
    {

        // $userid = auth()->user()->id;

        $first_balance = 1000000;
        $users = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'card' => $this->generateCardNumber(),
            'password' => bcrypt($request->password),
            'balance' => $first_balance,
        ]);

        auth()->login($users);
        $request->session()->regenerate();

        return redirect('/dashboards/');

    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
