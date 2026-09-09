<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Support\CatatAktivitas;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, \App\Services\CartService $cartService): RedirectResponse
    {
        $guestSessionId = session()->getId();

        $request->authenticate();

        $request->session()->regenerate();

        CatatAktivitas::tulisAuth('login', 'Customer berhasil masuk (login) ke akun', Auth::user());

        // Merge guest cart with user cart after login
        $cartService->mergeGuestCart($guestSessionId, Auth::id());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        CatatAktivitas::tulisAuth('logout', 'Customer keluar (logout) dari akun', Auth::user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
