<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Memeriksa — dan bila diminta, memperbaiki — kesiapan aplikasi di hosting.
 *
 * Dibuat karena galat pemasangan hampir selalu berujung layar 500 atau 419 yang
 * tidak menjelaskan apa pun, sementara sebabnya biasanya sepele: tabel yang
 * belum termigrasi, cache konfigurasi yang basi, folder yang tidak bisa ditulis,
 * atau APP_URL yang masih menunjuk alamat lokal.
 *
 * Perintah ini menyebutkan penyebabnya dalam bahasa manusia, lalu — dengan
 * --perbaiki — mengerjakan sendiri yang memang aman dikerjakan. Yang menyangkut
 * berkas .env tidak pernah diubah otomatis: isinya rahasia dan keputusan
 * pemiliknya, jadi hanya ditunjukkan apa yang perlu diganti.
 *
 *   php artisan record:periksa-hosting
 *   php artisan record:periksa-hosting --perbaiki
 */
class PeriksaHosting extends Command
{
    protected $signature = 'record:periksa-hosting
                            {--perbaiki : Jalankan perbaikan yang aman, bukan hanya memeriksa}';

    protected $description = 'Memeriksa kesiapan aplikasi di hosting dan memperbaiki yang bisa diperbaiki';

    /** Tabel yang harus ada agar aplikasi ini berjalan. */
    private const TABEL_WAJIB = [
        'users'              => 'akun pengguna',
        'sessions'           => 'penyimpanan sesi (SESSION_DRIVER=database)',
        'cache'              => 'penyimpanan cache (CACHE_STORE=database)',
        'orders'             => 'pesanan',
        'order_items'        => 'rincian pesanan',
        'products'           => 'produk',
        'carts'              => 'keranjang belanja',
        'cart_items'         => 'isi keranjang',
        'addresses'          => 'alamat pengiriman',
    ];

    /** Tabel dari fitur yang ditambahkan belakangan. */
    private const TABEL_FITUR = [
        'product_reviews'    => 'ulasan produk',
        'order_returns'      => 'pengajuan pengembalian',
        'rpay_transactions'  => 'buku besar R_Pay',
        'rpay_withdrawals'   => 'pengajuan pencairan R_Pay',
    ];

    private int $masalah = 0;
    private int $peringatan = 0;
    private array $saranEnv = [];

    public function handle(): int
    {
        $perbaiki = (bool) $this->option('perbaiki');

        $this->newLine();
        $this->line('  <fg=white;options=bold>PEMERIKSAAN KESIAPAN HOSTING</>');
        $this->line('  ' . config('app.name') . ' — ' . ($perbaiki ? 'mode PERBAIKI' : 'mode periksa saja'));
        $this->newLine();

        $this->periksaLingkungan();
        $this->periksaBasisData();
        $this->periksaTabel($perbaiki);
        $this->periksaSesiDanCache();
        $this->periksaBerkas($perbaiki);
        $this->periksaAset();

        if ($perbaiki) {
            $this->segarkanCache();
        }

        return $this->ringkasan($perbaiki);
    }

    // ── Bagian pemeriksaan ────────────────────────────────────────────────

    private function periksaLingkungan(): void
    {
        $this->judul('Lingkungan');

        $env   = (string) config('app.env');
        $debug = (bool) config('app.debug');
        $url   = (string) config('app.url');
        $kunci = (string) config('app.key');

        $this->nilai('APP_ENV', $env);

        if ($env !== 'production') {
            $this->awas('APP_ENV masih "' . $env . '". Di server seharusnya "production".');
            $this->saranEnv['APP_ENV'] = 'production';
        }

        if ($debug) {
            $this->gagal('APP_DEBUG masih menyala. Setiap galat akan menampilkan '
                . 'isi konfigurasi — termasuk kunci Midtrans dan sandi basis data — '
                . 'kepada siapa pun yang membuka halamannya.');
            $this->saranEnv['APP_DEBUG'] = 'false';
        } else {
            $this->baik('APP_DEBUG mati');
        }

        if ($kunci === '') {
            $this->gagal('APP_KEY kosong. Semua cookie gagal didekripsi, dan setiap '
                . 'kiriman borang akan berakhir 419. Jalankan: php artisan key:generate');
        } else {
            $this->baik('APP_KEY terisi');
        }

        $this->nilai('APP_URL', $url ?: '(kosong)');

        if (! str_starts_with($url, 'https://')) {
            $this->gagal('APP_URL bukan https. Borang akan dikirim ke http, server '
                . 'mengalihkannya ke https, dan pengalihan pada POST membuat isi '
                . 'kiriman beserta sesinya hilang — itulah 419.');
            $this->saranEnv['APP_URL'] = 'https://' . (parse_url($url, PHP_URL_HOST) ?: 'domainmu');
        } else {
            $this->baik('APP_URL memakai https');
        }

        if (str_contains($url, '.test') || str_contains($url, 'localhost')) {
            $this->gagal('APP_URL masih menunjuk alamat lokal (' . $url . ').');
        }

        // Nama cookie sesi tidak boleh sama dengan aplikasi tetangga.
        $cookie = (string) config('session.cookie');
        $this->nilai('Cookie sesi', $cookie);

        if (str_contains($cookie, 'laravel')) {
            $this->awas('Nama cookie sesi masih bawaan. Bila aplikasi tetangga memakai '
                . 'nama yang sama di induk domain yang sama, keduanya saling menimpa.');
        }

        $domain = config('session.domain');
        $host   = parse_url($url, PHP_URL_HOST);
        $this->nilai('SESSION_DOMAIN', $domain ?: '(null — hanya host ini)');

        if ($domain && $host && ! str_ends_with((string) $host, ltrim((string) $domain, '.'))) {
            $this->gagal('SESSION_DOMAIN (' . $domain . ') tidak cocok dengan host APP_URL ('
                . $host . '). Cookie sesinya tidak akan pernah terkirim balik.');
        }

        if (config('session.secure') && ! str_starts_with($url, 'https://')) {
            $this->gagal('SESSION_SECURE_COOKIE menyala tetapi APP_URL bukan https. '
                . 'Cookie sesi tidak akan pernah tersimpan.');
        }
    }

    private function periksaBasisData(): void
    {
        $this->judul('Basis data');

        try {
            DB::connection()->getPdo();
            $this->baik('Tersambung ke "' . DB::connection()->getDatabaseName() . '"');
        } catch (Throwable $e) {
            $this->gagal('Tidak bisa tersambung: ' . $e->getMessage());
        }
    }

    private function periksaTabel(bool $perbaiki): void
    {
        $this->judul('Tabel');

        $hilang = [];

        foreach (self::TABEL_WAJIB + self::TABEL_FITUR as $tabel => $guna) {
            try {
                if (! Schema::hasTable($tabel)) {
                    $hilang[$tabel] = $guna;
                }
            } catch (Throwable $e) {
                $this->gagal('Gagal memeriksa tabel ' . $tabel . ': ' . $e->getMessage());
                return;
            }
        }

        if ($hilang === []) {
            $this->baik('Semua tabel yang dibutuhkan sudah ada');
            return;
        }

        foreach ($hilang as $tabel => $guna) {
            $this->gagal('Tabel "' . $tabel . '" tidak ada — ' . $guna . '.');
        }

        if (! $perbaiki) {
            $this->line('      <fg=gray>Jalankan ulang dengan --perbaiki untuk menjalankan migrasi.</>');
            return;
        }

        $this->line('      Menjalankan migrasi...');

        try {
            $this->callSilent('migrate', ['--force' => true]);
            $this->baik('Migrasi selesai');

            $masihHilang = array_filter(
                array_keys($hilang),
                fn ($t) => ! Schema::hasTable($t)
            );

            if ($masihHilang !== []) {
                $this->gagal('Masih hilang setelah migrasi: ' . implode(', ', $masihHilang)
                    . '. Berkas migrasinya kemungkinan belum ikut terunggah.');
            } else {
                $this->masalah -= count($hilang);   // sudah tertangani
            }
        } catch (Throwable $e) {
            $this->gagal('Migrasi gagal: ' . $e->getMessage());
        }
    }

    private function periksaSesiDanCache(): void
    {
        $this->judul('Sesi & cache');

        $this->nilai('SESSION_DRIVER', (string) config('session.driver'));
        $this->nilai('CACHE_STORE', (string) config('cache.default'));

        // Cache benar-benar bisa ditulis dan dibaca?
        try {
            $tanda = 'periksa-hosting-' . uniqid();
            Cache::put($tanda, 'ya', 60);
            $terbaca = Cache::get($tanda) === 'ya';
            Cache::forget($tanda);

            $terbaca
                ? $this->baik('Cache bisa ditulis dan dibaca')
                : $this->gagal('Cache bisa ditulis tetapi tidak terbaca kembali.');
        } catch (Throwable $e) {
            $this->gagal('Cache tidak berfungsi: ' . $e->getMessage()
                . ' — pembatas percobaan masuk dan penanda saldo bergantung padanya.');
        }

        // Sesi database perlu tabelnya.
        if (config('session.driver') === 'database') {
            $tabel = (string) config('session.table', 'sessions');

            try {
                Schema::hasTable($tabel)
                    ? $this->baik('Tabel sesi "' . $tabel . '" ada')
                    : $this->gagal('Tabel sesi "' . $tabel . '" tidak ada — setiap login berakhir 419.');
            } catch (Throwable $e) {
                $this->gagal('Gagal memeriksa tabel sesi: ' . $e->getMessage());
            }
        }
    }

    private function periksaBerkas(bool $perbaiki): void
    {
        $this->judul('Berkas & folder');

        foreach ([
            storage_path('framework'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ] as $folder) {
            if (! is_dir($folder)) {
                if ($perbaiki && @mkdir($folder, 0775, true)) {
                    $this->baik('Dibuat: ' . $this->pendek($folder));
                    continue;
                }

                $this->gagal('Folder tidak ada: ' . $this->pendek($folder));
                continue;
            }

            is_writable($folder)
                ? $this->baik('Bisa ditulis: ' . $this->pendek($folder))
                : $this->gagal('TIDAK bisa ditulis: ' . $this->pendek($folder)
                    . ' — jalankan: chmod -R 775 storage bootstrap/cache');
        }

        // Tautan storage untuk gambar produk.
        $tautan = public_path('storage');

        if (file_exists($tautan)) {
            $this->baik('Tautan public/storage ada');
        } elseif ($perbaiki) {
            try {
                $this->callSilent('storage:link');
                file_exists($tautan)
                    ? $this->baik('Tautan public/storage dibuat')
                    : $this->awas('Tautan public/storage gagal dibuat — gambar mungkin tidak tampil.');
            } catch (Throwable $e) {
                $this->awas('storage:link gagal: ' . $e->getMessage());
            }
        } else {
            $this->awas('Tautan public/storage belum ada — gambar bisa tidak tampil.');
        }
    }

    private function periksaAset(): void
    {
        $this->judul('Aset tampilan');

        $manifest = public_path('build/manifest.json');

        if (file_exists($manifest)) {
            $this->baik('Hasil build Vite ada (' . date('d M Y', filemtime($manifest)) . ')');
        } else {
            $this->gagal('public/build/manifest.json tidak ada. Tampilan akan berantakan. '
                . 'Jalankan "npm run build" di komputermu lalu unggah folder public/build.');
        }
    }

    private function segarkanCache(): void
    {
        $this->judul('Menyegarkan cache');

        foreach ([
            'config:clear' => 'konfigurasi dibersihkan',
            'route:clear'  => 'rute dibersihkan',
            'view:clear'   => 'tampilan dibersihkan',
            'config:cache' => 'konfigurasi disusun ulang',
            'route:cache'  => 'rute disusun ulang',
            'view:cache'   => 'tampilan disusun ulang',
        ] as $perintah => $kabar) {
            try {
                $this->callSilent($perintah);
                $this->baik($kabar);
            } catch (Throwable $e) {
                $this->awas($perintah . ' gagal: ' . $e->getMessage());
            }
        }
    }

    // ── Penutup ───────────────────────────────────────────────────────────

    private function ringkasan(bool $perbaiki): int
    {
        $this->newLine();

        if ($this->saranEnv !== []) {
            $this->line('  <fg=yellow;options=bold>Perlu kamu ubah sendiri di berkas .env:</>');
            foreach ($this->saranEnv as $kunci => $nilai) {
                $this->line('      ' . $kunci . '=' . $nilai);
            }
            $this->line('  <fg=gray>Berkas .env tidak pernah diubah otomatis — isinya rahasia '
                . 'dan keputusanmu.</>');
            $this->line('  <fg=gray>Sesudah mengubahnya, jalankan lagi perintah ini dengan --perbaiki.</>');
            $this->newLine();
        }

        $this->galatTerakhir();

        if ($this->masalah > 0) {
            $this->line('  <fg=red;options=bold>' . $this->masalah . ' masalah</> '
                . 'dan <fg=yellow>' . $this->peringatan . ' peringatan</>.');

            if (! $perbaiki) {
                $this->line('  Coba: <options=bold>php artisan record:periksa-hosting --perbaiki</>');
            }

            $this->newLine();

            return self::FAILURE;
        }

        $this->line('  <fg=green;options=bold>Semua pemeriksaan lolos.</> '
            . ($this->peringatan > 0 ? $this->peringatan . ' peringatan ringan.' : ''));
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Menampilkan galat terakhir dari berkas catatan.
     *
     * Layar 500 di peramban sengaja tidak menyebut apa pun demi keamanan, jadi
     * satu-satunya keterangan yang berguna ada di sini. Yang dicetak hanya baris
     * pertama tiap galat — jejak tumpukannya panjang dan jarang membantu bagi
     * yang bukan pemrogram.
     */
    private function galatTerakhir(): void
    {
        $berkas = storage_path('logs/laravel.log');

        if (! is_file($berkas) || filesize($berkas) === 0) {
            return;
        }

        // Hanya bagian ekor yang dibaca — berkas catatan bisa sangat besar.
        $ukuran = filesize($berkas);
        $ambil  = min($ukuran, 200_000);

        $pegangan = @fopen($berkas, 'r');

        if (! $pegangan) {
            return;
        }

        fseek($pegangan, -$ambil, SEEK_END);
        $isi = (string) fread($pegangan, $ambil);
        fclose($pegangan);

        preg_match_all(
            '/^\[([\d\-: ]+)\].*?(ERROR|CRITICAL|EMERGENCY): (.+)$/m',
            $isi,
            $cocok,
            PREG_SET_ORDER
        );

        if ($cocok === []) {
            return;
        }

        $terakhir = array_slice($cocok, -3);

        $this->line('  <fg=magenta;options=bold>GALAT TERAKHIR DI CATATAN</>');

        foreach ($terakhir as $baris) {
            $waktu = $baris[1];
            $pesan = trim($baris[3]);

            // Jejak tumpukan dipotong; yang berguna ada di kalimat pertama.
            $pesan = preg_replace('/ in \/.*$/', '', $pesan);
            $pesan = mb_substr($pesan, 0, 260);

            $this->line('    <fg=gray>' . $waktu . '</fg=gray>');
            $this->line('    ' . $pesan);
            $this->newLine();
        }

        $this->line('  <fg=gray>Selengkapnya: tail -n 60 storage/logs/laravel.log</>');
        $this->newLine();
    }

    // ── Pembantu tampilan ─────────────────────────────────────────────────

    private function judul(string $teks): void
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>' . strtoupper($teks) . '</>');
    }

    private function baik(string $teks): void
    {
        $this->line('    <fg=green>OK</>   ' . $teks);
    }

    private function awas(string $teks): void
    {
        $this->peringatan++;
        $this->line('    <fg=yellow>AWAS</> ' . $teks);
    }

    private function gagal(string $teks): void
    {
        $this->masalah++;
        $this->line('    <fg=red>SALAH</> ' . $teks);
    }

    private function nilai(string $nama, string $isi): void
    {
        $this->line('    <fg=gray>·</>    ' . str_pad($nama, 16) . $isi);
    }

    private function pendek(string $jalur): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $jalur);
    }
}
