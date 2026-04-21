<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;

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
    
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function process(PaymentRequest $request, User $user): RedirectResponse
    {
        try {
            $this->paymentService->process($user, $request->validated());

            return redirect('dashboards');
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}



