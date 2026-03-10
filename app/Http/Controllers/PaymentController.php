<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function payment(User $user)
    {

        $users = User::all();

        return view('payments.payment', compact('users'));
    }

    public function window(User $user)
    {
        return view('payments.window')->with(['user' => $user]);
    }

    public function process(PaymentRequest $request, User $user)
    {
        $amount = $request->amount;
        $amount_with_percentage = round($amount * 0.10, 2);
        $overall = $amount + $amount_with_percentage;

        $profit = $user->balance + $amount;

        try {
            DB::transaction(function () use ($request, $user, $overall, $profit) {
                if (auth()->user()->balance < $overall) {
                    // выбрасываем исключение, чтобы откатить транзакцию
                    throw new \Exception('Недостаточно средств');
                }

                // Обновляем получателя
                $user->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'card' => $request->card,
                    'password' => $request->password,
                    'balance' => $user->balance + $overall,

                    'profits' => $profit,
                ]);

                // Создаём запись о платеже
                Payment::create([
                    'amount' => $overall,
                    'user_id' => $user->id,
                    'card' => $request->card,
                    'payer' => auth()->user()->id,
                    'positive' => true,
                    'negative' => false,
                ]);

                // Списываем деньги у текущего пользователя
                $currentBalance = auth()->user()->balance;
                $currentUserExpenses = auth()->user()->expenses;
                $newCurrentBalance = $currentBalance - $overall;
                auth()->user()->update([
                    'balance' => $newCurrentBalance,
                    'expenses' => $overall + $currentUserExpenses,
                ]);
            });

            return redirect('dashboards');
        } catch (\Exception $e) {
            // если ошибка — откат транзакции и возврат назад
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
