<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\AccountDeletionCodeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profil pengguna.
     */
    public function edit(Request $request): RedirectResponse
    {
        return Redirect::to(route('dashboard') . '#pengaturan-akun');
    }

    /**
     * Perbarui data informasi profil pengguna.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::to(route('dashboard') . '#pengaturan-akun')->with('status', 'profile-updated');
    }

    /**
     * Langkah 1: Verifikasi password & kirim 6-digit OTP ke email user.
     */
    public function requestDeletionCode(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Kata sandi akun wajib diisi.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
            ], 422);
        }

        // Buat 6-digit kode OTP
        $code = sprintf('%06d', random_int(100000, 999999));
        $cacheKey = 'acc_del_code_' . $user->id;

        Cache::put($cacheKey, [
            'code' => $code,
            'user_id' => $user->id,
        ], now()->addMinutes(10));

        try {
            Mail::to($user->email)->send(new AccountDeletionCodeMail($code, $user->name));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim email OTP hapus akun: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim kode verifikasi ke email. Silakan coba sesaat lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi telah dikirim ke ' . $user->email . '. Berlaku selama 10 menit.',
            'email'   => $user->email,
        ]);
    }

    /**
     * Langkah 2: Verifikasi kode OTP dan hapus akun (Soft Delete).
     */
    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Kode verifikasi 6 digit wajib diisi.',
        ]);

        $user = $request->user();
        $cacheKey = 'acc_del_code_' . $user->id;
        $cached = Cache::get($cacheKey);

        if (!$cached || ($cached['code'] ?? '') !== trim($request->code)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode verifikasi salah atau sudah kedaluwarsa. Silakan minta kode baru.',
                ], 422);
            }
            return back()->withErrors(['code' => 'Kode verifikasi salah atau sudah kedaluwarsa.']);
        }

        // Hapus cache token
        Cache::forget($cacheKey);

        Auth::logout();

        // Soft delete user sehingga histori tetap tersimpan untuk admin
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => url('/'),
                'message' => 'Akun Anda telah berhasil dinonaktifkan. Terima kasih telah menggunakan layanan RECORD Shoes.',
            ]);
        }

        return Redirect::to('/')->with('success', 'Akun Anda telah berhasil dihapus. Terima kasih.');
    }
}