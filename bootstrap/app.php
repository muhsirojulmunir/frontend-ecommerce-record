<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Webhook Midtrans datang dari peladen mereka, bukan dari peramban
         * pembeli, jadi tidak mungkin membawa token CSRF. Keasliannya dijaga
         * oleh tanda tangan SHA-512 yang diperiksa di MidtransService.
         *
         * Kedua alamat didaftarkan karena keduanya memang terpasang sebagai
         * rute. Yang kedua sebelumnya terlewat — kalau alamat itu yang
         * didaftarkan di dasbor Midtrans, notifikasinya ditolak 419 dan
         * pesanan tidak pernah tercatat lunas secara otomatis.
         */
        $middleware->validateCsrfTokens(except: [
            'midtrans/callback',
            'checkout/midtrans/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Sesi kedaluwarsa (galat 419).
         *
         * Halaman seperti checkout sering dibiarkan terbuka lama sementara
         * pembeli menyiapkan data. Bila sesinya keburu habis, token CSRF
         * ditolak dan Laravel menampilkan halaman "419 Page Expired" yang
         * membingungkan — isian pembeli pun hilang.
         *
         * Di sini permintaan dikembalikan ke halaman asal beserta isiannya
         * (kecuali kata sandi), disertai pesan yang bisa dimengerti, sehingga
         * pembeli cukup menekan tombolnya sekali lagi.
         */
        // Laravel sudah menerjemahkan TokenMismatchException menjadi HttpException
        // berkode 419 sebelum callback ini dijalankan, jadi yang dicocokkan
        // adalah kode statusnya. Selain 419, biarkan penanganan bawaan berjalan.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi kamu sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.',
                ], 419);
            }

            // Sesi lama sudah hilang, sehingga url()->previous() belum tentu
            // benar. Untuk langkah-langkah checkout, kembalikan langsung ke
            // halaman checkout supaya pembeli tidak terlempar ke beranda.
            $tujuan = $request->is('checkout', 'checkout/*')
                ? route('checkout.index')
                : url()->previous();

            return redirect($tujuan)
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', 'Sesi halaman sudah kedaluwarsa karena dibiarkan terlalu lama. '
                    . 'Data yang kamu isi masih tersimpan — silakan tekan tombolnya sekali lagi.');
        });
    })->create();
