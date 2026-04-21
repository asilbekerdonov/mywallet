<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private const COMMISSION_RATE = 0.10;

    public function process(User $recipient, array $data): void
    {
        $amount     = $data['amount'];
        $commission = round($amount * self::COMMISSION_RATE, 2);
        $total      = $amount + $commission;

        DB::transaction(function () use ($recipient, $data, $amount, $total) {
            $payer = auth()->user();

            if ($payer->balance < $total) {
                throw new \RuntimeException('Недостаточно средств');
            }

            // Начисляем получателю
            $recipient->update([
                'balance' => $recipient->balance + $total,
                'profits' => $recipient->balance + $amount,
            ]);

            // Фиксируем платёж
        Payment::create([
            'amount'   => $total,
            'user_id'  => $recipient->id,
            'card'     => $recipient->card,  // ← берём с модели получателя
            'payer'    => $payer->id,
            'positive' => true,
            'negative' => false,
]);

            // Списываем у плательщика
            $payer->update([
                'balance'  => $payer->balance - $total,
                'expenses' => $payer->expenses + $total,
            ]);
        });
    }
}