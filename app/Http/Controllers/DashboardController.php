<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $totalOrders    = Order::where('user_id', $user->id)->count();
        $totalSpent     = Order::where('user_id', $user->id)->where('payment_status', 'paid')->sum('grand_total');
        $pendingOrders  = Order::where('user_id', $user->id)->where('status', 'pending')->count();

        /*
         * Kode referal & R_Pay.
         *
         * Keabsahan kode dihitung ulang di sini, bukan sekadar melihat apakah
         * kolomnya terisi: kode yang pesanannya batal harus terlihat hangus
         * oleh pemiliknya sendiri, bukan cuma oleh orang yang memakainya.
         */
        $referral = app(\App\Services\ReferralService::class);

        $kodeReferal   = $user->referral_code;
        $referalAktif  = filled($kodeReferal) && $referral->punyaPesananSah($user);

        $dipakaiOrang  = Order::where('referrer_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->count();

        $komisiDiterima = (float) \App\Models\RpayTransaction::where('user_id', $user->id)
            ->where('source', 'referral')
            ->sum('amount')
            - (float) \App\Models\RpayTransaction::where('user_id', $user->id)
                ->where('source', 'referral_reversal')
                ->sum('amount');

        $saldoRpay = app(\App\Services\RpayService::class)->saldo($user);

        return view('dashboard', compact(
            'recentOrders', 'totalOrders', 'totalSpent', 'pendingOrders',
            'kodeReferal', 'referalAktif', 'dipakaiOrang', 'komisiDiterima', 'saldoRpay'
        ));
    }
}
