<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Support\CatatAktivitas;
use App\Support\KalenderKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Pengajuan pengembalian barang oleh pembeli.
 */
class ReturnController extends Controller
{
    public function store(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $this->bolehMengajukan($order)) {
            return back()->with('error', $this->alasanTidakBoleh($order));
        }

        $kodeAlasan = collect(config('alasan-retur.pilihan'))->pluck('kode')->all();
        $kodeWajib  = config('alasan-retur.wajib_penjelasan', 'lainnya');
        $minimal    = (int) config('alasan-retur.minimal_penjelasan', 15);

        $megaFoto   = (int) config('alasan-retur.bukti.maks_foto_mb', 2);
        $megaVideo  = (int) config('alasan-retur.bukti.maks_video_mb', 10);
        $batasFoto  = $megaFoto * 1024;
        $batasVideo = $megaVideo * 1024;
        $batasDetik = (int) config('alasan-retur.bukti.maks_durasi_detik', 120);

        // Penjelasan bebas hanya wajib untuk "Alasan lain".
        // Kegagalan unggah di tingkat PHP diperiksa lebih dulu.
        if ($galat = $this->galatUnggah($request)) {
            return back()->withInput()->withErrors($galat);
        }

        $data = $request->validate([
            'reason_code'   => ['required', Rule::in($kodeAlasan)],
            'reason'        => ['nullable', 'required_if:reason_code,' . $kodeWajib,
                                'string', 'min:' . $minimal, 'max:2000'],
            'resolution'    => ['required', Rule::in(['refund', 'exchange'])],
            'exchange_request' => ['required_if:resolution,exchange', 'nullable', 'string', 'max:255'],

            // Tiga bukti wajib.
            'receipt_photo'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . $batasFoto],
            'package_photo'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . $batasFoto],
            'unboxing_video' => ['required', 'file', 'mimes:mp4,mov,webm,m4v', 'max:' . $batasVideo],

            // Durasi diukur di peramban lalu dikirim bersama berkasnya. Nilainya
            // tidak dipercaya sebagai satu-satunya penjaga — ukuran berkas di
            // atas sudah membatasi dari sisi peladen — tetapi cukup untuk
            // menolak video kepanjangan tanpa memasang pengurai video.
            'video_duration' => ['nullable', 'integer', 'min:1', 'max:' . $batasDetik],
        ], [
            'reason_code.required'      => 'Pilih dulu alasan pengembaliannya.',
            'reason.required_if'        => 'Karena memilih "Alasan lain", tuliskan dulu alasannya di kolom penjelasan.',
            'reason.min'                => 'Penjelasannya terlalu singkat, mohon tuliskan minimal :min karakter.',
            'resolution.required'       => 'Pilih mau diselesaikan dengan tukar barang atau pengembalian dana.',
            'exchange_request.required_if' => 'Sebutkan barang atau ukuran pengganti yang kamu inginkan.',

            'receipt_photo.required'  => 'Foto resi wajib dilampirkan.',
            'receipt_photo.image'     => 'Foto resi harus berupa gambar (JPG, PNG, atau WebP).',
            'receipt_photo.max'       => 'Ukuran foto resi maksimal ' . $megaFoto . ' MB.',
            'package_photo.required'  => 'Foto paket wajib dilampirkan.',
            'package_photo.image'     => 'Foto paket harus berupa gambar (JPG, PNG, atau WebP).',
            'package_photo.max'       => 'Ukuran foto paket maksimal ' . $megaFoto . ' MB.',
            'unboxing_video.required' => 'Video unboxing wajib dilampirkan.',
            'unboxing_video.mimes'    => 'Video unboxing harus berformat MP4, MOV, atau WebM.',
            'unboxing_video.max'      => 'Ukuran video maksimal ' . $megaVideo . ' MB.',
            'video_duration.max'      => 'Durasi video unboxing maksimal ' . ($batasDetik / 60) . ' menit.',
        ]);

        // Penyelesaian yang diminta harus masuk akal untuk alasannya —
        // "berubah pikiran", misalnya, tidak bisa diselesaikan dengan
        // tukar barang karena barangnya sendiri tidak bermasalah.
        $pilihan = collect(config('alasan-retur.pilihan'))->firstWhere('kode', $data['reason_code']);

        if (! in_array($data['resolution'], $pilihan['penyelesaian'] ?? [], true)) {
            return back()
                ->withInput()
                ->with('error', 'Untuk alasan "' . $pilihan['label'] . '", penyelesaian itu tidak tersedia.');
        }

        // Berkas ditulis ke cakram "bersama", yaitu folder storage panel admin — bukan storage toko ini.
        $folder = 'retur/' . $order->order_number;

        $berkas = [
            'receipt_photo'  => $request->file('receipt_photo')->store($folder, 'bersama'),
            'package_photo'  => $request->file('package_photo')->store($folder, 'bersama'),
            'unboxing_video' => $request->file('unboxing_video')->store($folder, 'bersama'),
            'video_duration' => $data['video_duration'] ?? null,
        ];

        $pengajuan = OrderReturn::create([
            'order_id'      => $order->id,
            'user_id'       => Auth::id(),
            'type'          => 'return',
            'reason_code'   => $data['reason_code'],
            // Kosong disimpan sebagai null, bukan string kosong, supaya
            // "tidak diisi" bisa dibedakan dari "diisi lalu dikosongkan".
            'reason'        => filled($data['reason'] ?? null) ? trim($data['reason']) : null,
            'resolution'    => $data['resolution'],
            'exchange_request' => $data['resolution'] === 'exchange' ? $data['exchange_request'] : null,
            'status'        => 'pending',
        ] + $berkas);

        CatatAktivitas::tulis(
            'pengembalian',
            'mengajukan pengembalian pesanan: ' . $order->order_number,
            $pengajuan,
            [
                'alasan'       => $data['reason_code'],
                'penyelesaian' => $data['resolution'],
            ],
            'created'
        );

        // Tenggat ditulis sebagai tanggal, bukan sekadar "1-2 hari kerja".
        // Pembeli tidak perlu menghitung sendiri hari libur di antaranya.
        $perkiraan = KalenderKerja::setelah((int) config('alasan-retur.konfirmasi_hari_kerja', 2));

        return redirect()
            ->route('orders.show', $order->order_number)
            ->with('success', 'Pengajuan pengembalian berhasil dikirim. '
                . 'Admin akan mengonfirmasi dalam 1-2 hari kerja, perkiraan paling lambat '
                . $perkiraan->translatedFormat('l, d F Y') . '. '
                . 'Hari Minggu dan tanggal merah tidak dihitung. '
                . 'Perkembangannya bisa kamu pantau di halaman ini.');
    }

    /**
 * Memeriksa kegagalan unggah di tingkat PHP, sebelum validasi biasa.
 */
    private function galatUnggah(Request $request): ?array
    {
        $medan = [
            'receipt_photo'  => 'Foto resi',
            'package_photo'  => 'Foto paket',
            'unboxing_video' => 'Video unboxing',
        ];

        $galat = [];

        foreach ($medan as $nama => $sebutan) {
            $berkas = $request->file($nama);

            if (! $berkas || $berkas->isValid()) {
                continue;
            }

            $kode = $berkas->getError();

            // Dicatat ke log dengan angka galatnya. Tanpa ini, laporan
            // "gagal terus" dari pembeli tidak bisa ditelusuri sama sekali.
            Log::warning('Unggahan bukti pengembalian gagal', [
                'medan'               => $nama,
                'kode_galat'          => $kode,
                'keterangan_php'      => $berkas->getErrorMessage(),
                'nama_berkas'         => $berkas->getClientOriginalName(),
                'ukuran_dilaporkan'   => $berkas->getSize(),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size'       => ini_get('post_max_size'),
                'upload_tmp_dir'      => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
            ]);

            $galat[$nama] = match ($kode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                    $sebutan . ' terlalu besar untuk diterima peladen (batasnya '
                    . ini_get('upload_max_filesize') . '). Coba pilih ulang berkasnya.',

                UPLOAD_ERR_PARTIAL =>
                    $sebutan . ' terkirim sebagian saja — sambungannya terputus di tengah jalan. '
                    . 'Coba kirim ulang.',

                UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE =>
                    $sebutan . ' tidak bisa disimpan sementara di peladen. '
                    . 'Ini masalah di sisi kami — mohon hubungi kami dan kami perbaiki.',

                UPLOAD_ERR_EXTENSION =>
                    $sebutan . ' ditolak oleh peladen. Mohon hubungi kami.',

                default =>
                    $sebutan . ' gagal terkirim (kode ' . $kode . '). Coba pilih ulang berkasnya.',
            };
        }

        return $galat ?: null;
    }

    /**
 * Pembeli mencatatkan nomor resi pengiriman balik.
 */
    public function kirimBalik(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $pengajuan = $order->returns()->where('type', 'return')->firstOrFail();

        if ($pengajuan->status !== 'approved') {
            return back()->with('error', $pengajuan->status === 'pending'
                ? 'Pengajuanmu belum dikonfirmasi admin. Tunggu konfirmasinya dulu.'
                : 'Nomor resi untuk pengajuan ini sudah pernah dicatat.');
        }

        $data = $request->validate([
            'return_courier'         => ['required', 'string', 'max:100'],
            'return_tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'return_courier.required'         => 'Sebutkan kurir yang kamu pakai.',
            'return_tracking_number.required' => 'Nomor resi wajib diisi agar paketnya bisa kami lacak.',
        ]);

        $pengajuan->update([
            'status'                 => 'shipped_back',
            'return_courier'         => trim($data['return_courier']),
            'return_tracking_number' => trim($data['return_tracking_number']),
            'shipped_back_at'        => now(),
        ]);

        CatatAktivitas::tulis(
            'pengembalian',
            'mengirim balik barang pengembalian: ' . $order->order_number,
            $pengajuan,
            ['kurir' => $data['return_courier'], 'resi' => $data['return_tracking_number']],
            'updated'
        );

        return redirect()
            ->route('orders.show', $order->order_number)
            ->with('success', 'Nomor resi tersimpan. Kami akan memberi kabar begitu barangnya sampai '
                . 'dan selesai diperiksa.');
    }

    /**
     * Pengajuan hanya dibuka setelah barang sampai, dan ditutup kembali
     * setelah lewat batas hari sejak pesanan dinyatakan selesai.
     */
    public static function bolehMengajukan(Order $order): bool
    {
        // Satu pesanan hanya boleh punya satu pengajuan, baik yang masih
        // berjalan maupun yang sudah diputuskan.
        if ($order->returns()->where('type', 'return')->exists()) {
            return false;
        }

        if ($order->status === 'shipped') {
            return true;
        }

        if ($order->status !== 'completed') {
            return false;
        }

        $batas = (int) config('alasan-retur.batas_hari', 7);
        $sejak = $order->completed_at ?? $order->updated_at;

        return $sejak && $sejak->diffInDays(now()) <= $batas;
    }

    private function alasanTidakBoleh(Order $order): string
    {
        if ($order->returns()->where('type', 'return')->exists()) {
            return 'Pesanan ini sudah pernah diajukan pengembalian.';
        }

        if (! in_array($order->status, ['shipped', 'completed'], true)) {
            return 'Pengembalian baru bisa diajukan setelah barang sampai.';
        }

        return 'Batas waktu pengajuan pengembalian untuk pesanan ini sudah lewat ('
            . config('alasan-retur.batas_hari', 7) . ' hari sejak pesanan selesai).';
    }
}
