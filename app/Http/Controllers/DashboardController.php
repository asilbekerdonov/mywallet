<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/post-photos');
        }

        $request->validate([
            'username' => 'required',
            'email' => 'required',
        ]);
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        if ($request->hasFile('photo')) {
            $user->photo = $path;
        }
        $user->save();

        return redirect()->back();
    }

    public function passwordupdate(Request $request, string $id)
    {

        $user = User::find($id);
        $request->validate([
            'currentpassword' => 'required',
            'newpassword' => 'required',
        ]);

        $currentpassword = auth()->user()->password;
        $plainPassword = $request->input('currentpassword');

        if (Hash::check($plainPassword, $currentpassword)) {
            $user->password = $request->input('newpassword');
            $user->save();

            return redirect()->back();
        } else {
            echo 'Error';
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function account()
    {
        $user_id = auth()->user()->id;

        return view('dashboards.users', compact('user_id'));
    }
}
