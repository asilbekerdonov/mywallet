<?php

namespace App\Services;

class CardService
{
    public function generateCardNumber(): string
    {
        $prefix = '8600';
        $randomDigits = '';

        for ($i = 0; $i < 12; $i++) {
            $randomDigits .= mt_rand(0, 9);
        }

        return $prefix . ' '
            . substr($randomDigits, 0, 4) . ' '
            . substr($randomDigits, 4, 4) . ' '
            . substr($randomDigits, 8, 4);
    }
}