<?php

namespace App\Http\Controllers;

use App\Exceptions\SaldoTidakCukup;
use App\Models\RpayWithdrawal;
use App\Services\RpayService;
use App\Support\CatatAktivitas;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * R_Pay — dompet digital pembeli.
 */
class RpayController extends Controller
{
    public function __construct(private RpayService $rpay)
    {
    }

    public function index()
    {
        $pemilik = Auth::user();

        return view('rpay.index', [
            'saldo'      => $this->rpay->saldo($pemilik),
            'mutasi'     => $pemilik->rpayTransactions()->paginate(15),
            'pencairan'  => $pemilik->rpayWithdrawals()->take(5)->get(),
            'minimum'    => (int) config('rpay.pencairan.minimum', 50000),
            'daftarBank' => config('rpay.bank', []),
            'perkiraan'  => $this->rpay->perkiraanCair(),
        ]);
    }

    /**
 * Mengajukan pencairan ke rekening bank.
 */
    public function withdraw(Request $request)
    {
        $minimum = (int) config('rpay.pencairan.minimum', 50000);
        $pemilik = Auth::user();

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:' . $minimum],
            'bank_name'      => ['required', Rule::in(config('rpay.bank', []))],
            'account_number' => ['required', 'string', 'regex:/^[0-9]{6,20}$/'],
            'account_holder' => ['required', 'string', 'max:255'],
        ], [
            'amount.min'             => 'Pencairan minimal Rp ' . number_format($minimum, 0, ',', '.') . '.',
            'bank_name.in'           => 'Pilih bank dari daftar yang tersedia.',
            'account_number.regex'   => 'Nomor rekening hanya boleh berisi angka (6-20 digit).',
            'account_holder.required' => 'Isi nama pemilik rekening sesuai buku tabungan.',
        ]);

        // Antrean ganda dilarang: satu pengajuan diselesaikan dulu, supaya
        // pembeli tidak bingung mana yang sedang diproses.
        if ($pemilik->rpayWithdrawals()->whereIn('status', ['pending', 'processing'])->exists()) {
            return back()->withInput()->with('error',
                'Masih ada pengajuan pencairan yang sedang diproses. '
                . 'Tunggu sampai selesai sebelum mengajukan lagi.');
        }

        try {
            $pencairan = DB::transaction(function () use ($data, $pemilik) {
                $pencairan = RpayWithdrawal::create([
                    'user_id'            => $pemilik->id,
                    'reference'          => RpayWithdrawal::buatReferensi(),
                    'amount'             => $data['amount'],
                    'bank_name'          => $data['bank_name'],
                    'account_number'     => $data['account_number'],
                    'account_holder'     => $data['account_holder'],
                    'status'             => 'pending',
                    'estimated_ready_at' => $this->rpay->perkiraanCair(CarbonImmutable::now()),
                ]);

                $this->rpay->debit(
                    $pemilik->id,
                    (float) $data['amount'],
                    'withdrawal',
                    'Pencairan ke ' . $data['bank_name'] . ' (' . $pencairan->reference . ')',
                    $pencairan,
                    $pemilik->id
                );

                return $pencairan;
            });
        } catch (SaldoTidakCukup $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        CatatAktivitas::tulis(
            'rpay',
            'mengajukan pencairan R_Pay: ' . $pencairan->reference,
            $pencairan,
            ['nominal' => $data['amount'], 'bank' => $data['bank_name']],
            'created'
        );

        return redirect()->route('rpay.index')->with('success',
            'Pengajuan pencairan ' . $pencairan->reference . ' sudah terkirim. '
            . 'Perkiraan dana sampai ' . $pencairan->estimated_ready_at->translatedFormat('l, d F Y') . '.');
    }
}
