<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'username' => 'admin',
            'email' => 'a@',
            'balance' => '0',
            'card' => '0000 0000 0000 0000',
            'password' => '777',
        ]);

    }
}
