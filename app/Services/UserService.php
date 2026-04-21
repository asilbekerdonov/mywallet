<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function updateInfo(User $user, Request $request): void
    {
        $user->username = $request->input('username');
        $user->email    = $request->input('email');

        if ($request->hasFile('photo')) {
            $user->photo = $request->file('photo')
                ->store('public/post-photos');
        }

        $user->save();
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        return true;
    }
}