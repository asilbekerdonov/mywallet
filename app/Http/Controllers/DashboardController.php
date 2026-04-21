<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Payment;
class DashboardController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index()
    {
        $user = auth()->user();

        $profits = $user->profits ?? 0;
        $expenses = $user->expenses ?? 0;

        // Формируем массив для графика
        $data = [
            ['value' => $profits, 'name' => 'Profits'],
            ['value' => $expenses, 'name' => 'Expenses'],
        ];

        $payments = Payment::all();

        return view('dashboards.dashboard', compact('payments', 'data'));
    }

    public function account()
    {
        $user_id = auth()->user()->id;

        return view('dashboards.users', compact('user_id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'username' => ['required'],
            'email'    => ['required', 'email'],
        ]);

        $user = User::findOrFail($id);
        $this->userService->updateInfo($user, $request);

        return redirect()->back();
    }

    public function passwordupdate(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'currentpassword' => ['required'],
            'newpassword'     => ['required', 'min:8'],
        ]);

        $user = User::findOrFail($id);

        $updated = $this->userService->updatePassword(
            $user,
            $request->input('currentpassword'),
            $request->input('newpassword')
        );

        if (! $updated) {
            return redirect()->back()
                ->withErrors(['currentpassword' => 'Текущий пароль неверен.']);
        }

        return redirect()->back()->with('success', 'Пароль успешно изменён.');
    }
}