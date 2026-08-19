<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the new password view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->hasVerifiedSession($request)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', ['email' => $request->session()->get('password_reset_email')]);
    }

    /**
 * Handle an incoming new password request.
 *
 * @throws ValidationException
 */
    public function store(Request $request): RedirectResponse
    {
        if (! $this->hasVerifiedSession($request)) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('password_reset_email');
        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()->route('login')->with('status', 'Password berhasil diperbarui. Silakan masuk.');
    }

    /**
     * Determine whether the session has a verified reset code.
     */
    private function hasVerifiedSession(Request $request): bool
    {
        return $request->session()->get('password_reset_email')
            && $request->session()->get('password_reset_verified');
    }
}
