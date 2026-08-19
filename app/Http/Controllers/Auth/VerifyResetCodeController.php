<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VerifyResetCodeController extends Controller
{
    /**
     * Display the code verification view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-reset-code', ['email' => $email]);
    }

    /**
     * Verify the submitted 5-digit code.
     */
    public function store(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'code' => ['required', 'digits:5'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        $expired = ! $record || now()->diffInMinutes($record->created_at) > 10;

        if ($expired || ! Hash::check($request->code, $record->token)) {
            return back()->withErrors(['code' => 'Kode verifikasi salah atau sudah kedaluwarsa.']);
        }

        $request->session()->put('password_reset_verified', true);

        return redirect()->route('password.reset');
    }

    /**
     * Resend a fresh verification code to the same email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        PasswordResetLinkController::sendCode($email);

        return back()->with('status', 'Kode baru telah dikirim ke email Anda.');
    }
}
