<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
 * Handle an incoming password reset code request.
 *
 * @throws ValidationException
 */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $this->sendCode($request->email);

        $request->session()->put('password_reset_email', $request->email);

        return redirect()->route('password.verify')
            ->with('status', 'Kode verifikasi telah dikirim ke email Anda.');
    }

    /**
     * Generate a fresh 5-digit code, store its hash, and email it.
     */
    public static function sendCode(string $email): void
    {
        $code = (string) random_int(10000, 99999);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($code),
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new PasswordResetCodeMail($code));
    }
}
