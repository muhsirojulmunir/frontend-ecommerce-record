<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengajuan pengembalian barang atau pembatalan pesanan.
 *
 * Kolom "reason" menyimpan penjelasan yang ditulis sendiri oleh pembeli,
 * sedangkan "reason_code" menyimpan alasan yang dipilih dari daftar. Keduanya
 * dipisah supaya alasan bisa dihitung untuk laporan tanpa harus menebak dari
 * teks bebas.
 */
class OrderReturn extends Model
{
    protected $fillable = [
        'return_number',
        'order_id',
        'user_id',
        'type',
        'reason_code',
        'reason',
        'receipt_photo',
        'package_photo',
        'unboxing_video',
        'video_duration',
        'resolution',
        'exchange_request',
        'refund_amount',
        'status',
        'approved_at',
        'return_courier',
        'return_tracking_number',
        'shipped_back_at',
        'received_at',
        'inspection_notes',
        'admin_notes',
        'decided_by',
        'resolved_at',
    ];

    /**
     * Nomor pengembalian berurutan per hari, contoh: RET-20260814-0001.
     *
     * Formatnya sengaja meniru nomor pesanan supaya polanya seragam dan
     * langsung dikenali admin sekilas. Dibuat lewat kait model, bukan di
     * controller, agar pengajuan dari jalur mana pun — pembeli, admin,
     * maupun pengisian data — selalu punya nomor.
     */
    public static function buatNomor(): string
    {
        $tanggal = now()->format('Ymd');
        $awalan  = 'RET-' . $tanggal . '-';

        $terakhir = static::where('return_number', 'like', $awalan . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (self $pengajuan) {
            if (blank($pengajuan->return_number)) {
                $pengajuan->return_number = static::buatNomor();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'resolved_at'     => 'datetime',
            'approved_at'     => 'datetime',
            'shipped_back_at' => 'datetime',
            'received_at'     => 'datetime',
            'refund_amount'   => 'decimal:2',
        ];
    }

    /**
     * Urutan tahapan yang dilalui pengajuan yang berjalan normal.
     * "rejected" sengaja di luar daftar karena bisa terjadi di tahap mana pun.
     */
    public const TAHAPAN = ['pending', 'approved', 'shipped_back', 'received', 'completed'];

    public function getTahapKeAttribute(): int
    {
        $urutan = array_search($this->status, self::TAHAPAN, true);

        return $urutan === false ? 0 : $urutan;
    }

    public function getDitolakAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    public function getSelesaiAttribute(): bool
    {
        return in_array($this->status, ['completed', 'rejected'], true);
    }

    /** Masih berjalan — pesanan tidak boleh diajukan ulang atau ditutup. */
    public function getBerjalanAttribute(): bool
    {
        return ! $this->selesai;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diputuskanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'      => 'Menunggu Konfirmasi Admin',
            'approved'     => 'Menunggu Barang Dikirim Kembali',
            'shipped_back' => 'Barang Dalam Perjalanan ke Kami',
            'received'     => 'Barang Sedang Diperiksa',
            'completed'    => 'Selesai',
            'rejected'     => 'Ditolak',
            default        => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending', 'approved' => 'warning',
            'shipped_back', 'received' => 'info',
            'completed' => 'success',
            'rejected'  => 'danger',
            default     => 'secondary',
        };
    }

    /**
     * Langkah-langkah yang ditampilkan sebagai timeline ke pembeli.
     *
     * Setiap langkah membawa keterangan yang menjelaskan apa yang sedang
     * terjadi dan apa yang perlu pembeli lakukan — bukan sekadar nama tahap,
     * supaya pembeli tidak perlu menebak giliran siapa sekarang.
     */
    public function timeline(): array
    {
        $tahap = $this->tahap_ke;

        $langkah = [
            [
                'kode'       => 'pending',
                'judul'      => 'Tunggu Konfirmasi Admin',
                'keterangan' => 'Pengajuanmu sedang kami tinjau. Perkiraan 1-2 hari kerja.',
                'ikon'       => 'fa-hourglass-half',
                'waktu'      => $this->created_at,
            ],
            [
                'kode'       => 'approved',
                'judul'      => 'Kirim Barang Kembali',
                'keterangan' => 'Pengajuan disetujui. Kirim barangnya ke alamat kami, lalu isi nomor resinya. Ongkos kirim ditanggung pembeli.',
                'ikon'       => 'fa-box',
                'waktu'      => $this->approved_at,
            ],
            [
                'kode'       => 'shipped_back',
                'judul'      => 'Barang Dalam Perjalanan',
                'keterangan' => 'Resi sudah kami terima. Menunggu barangnya sampai di gudang kami.',
                'ikon'       => 'fa-truck',
                'waktu'      => $this->shipped_back_at,
            ],
            [
                'kode'       => 'received',
                'judul'      => 'Barang Diperiksa',
                'keterangan' => 'Barang sudah sampai dan sedang kami periksa kondisinya.',
                'ikon'       => 'fa-magnifying-glass',
                'waktu'      => $this->received_at,
            ],
            [
                'kode'       => 'completed',
                'judul'      => $this->resolution === 'refund' ? 'Dana Dikembalikan' : 'Barang Pengganti Dikirim',
                'keterangan' => $this->resolution === 'refund'
                    ? 'Dana masuk ke saldo R_Pay-mu.'
                    : 'Barang penggantinya kami kirimkan.',
                'ikon'       => $this->resolution === 'refund' ? 'fa-wallet' : 'fa-right-left',
                'waktu'      => $this->status === 'completed' ? $this->resolved_at : null,
            ],
        ];

        foreach ($langkah as $i => $isi) {
            $langkah[$i]['selesai'] = ! $this->ditolak && $i < $tahap;
            $langkah[$i]['sekarang'] = ! $this->ditolak && $i === $tahap;
            $langkah[$i]['menunggu'] = $this->ditolak || $i > $tahap;
        }

        return $langkah;
    }

    public function getResolutionLabelAttribute(): string
    {
        return match ($this->resolution) {
            'refund'   => 'Pengembalian Dana ke R_Pay',
            'exchange' => 'Tukar Barang / Ukuran',
            default    => '—',
        };
    }

    /** Label alasan terpilih, diambil dari daftar di config. */
    public function getReasonLabelAttribute(): string
    {
        foreach (config('alasan-retur.pilihan', []) as $pilihan) {
            if (($pilihan['kode'] ?? null) === $this->reason_code) {
                return $pilihan['label'];
            }
        }

        return $this->reason_code ?: '—';
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'pending');
    }
}
