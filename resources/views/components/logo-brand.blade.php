@props([
    'varian' => 'terang',   // terang = latar putih (navbar) | gelap = latar gelap (footer)
    'ukuran' => 'sedang',   // kecil | sedang | besar
])

@php
    /*
     * Berkas logo dicari sekali di sini, bukan dirangkai berulang di tiap
     * tempat pemakaian. Urutannya dari format yang paling tajam: SVG tidak
     * pecah di layar beresolusi tinggi, WebP paling ringan, PNG paling umum.
     */
    /*
     * Satu berkas logo untuk semua tempat — warnanya tidak pernah diubah.
     *
     * Biru tetap biru, merah tetap merah. Di latar gelap, keterbacaannya
     * diurus dengan memberi alas terang di belakang logonya (lihat
     * .logo-brand-gelap di bawah), bukan dengan mewarnai ulang logonya.
     * Mewarnai ulang berarti mengubah lambang mereknya sendiri, dan itu
     * bukan wewenang halaman yang sekadar menampilkannya.
     */
    $kandidat = [
        'logo-brand-rapi.svg',   // hasil pangkas, dipakai lebih dulu
        'logo-brand-rapi.webp',
        'logo-brand-rapi.png',
        'Logo_Brand.svg',        // berkas asli sebagai cadangan
        'Logo_Brand.webp',
        'Logo_Brand.png',
        'Logo_Brand.jpg',
    ];

    $berkasLogo = collect($kandidat)
        ->first(fn ($nama) => file_exists(public_path('images/' . $nama)));

    $kelasUkuran = [
        'kecil'  => 'logo-brand-kecil',
        'sedang' => 'logo-brand-sedang',
        'besar'  => 'logo-brand-besar',
    ][$ukuran] ?? 'logo-brand-sedang';

    $kelasVarian = $varian === 'gelap' ? 'logo-brand-gelap' : 'logo-brand-terang';
@endphp

@if($berkasLogo)
    {{-- Alamat gambar diteruskan sebagai peubah CSS supaya kilaunya bisa
         memakai logo itu sendiri sebagai topeng — cahayanya menyapu persis
         di atas hurufnya, bukan di atas kotak persegi di sekelilingnya. --}}
    <span {{ $attributes->merge(['class' => "logo-brand {$kelasUkuran} {$kelasVarian}"]) }}
          style="--berkas-logo: url('{{ asset('images/' . $berkasLogo) }}')">
        <img src="{{ asset('images/' . $berkasLogo) }}"
             alt="{{ config('app.name', 'Record') }}"
             class="logo-brand-gambar"
             draggable="false">
        <span class="logo-brand-kilau" aria-hidden="true"></span>
    </span>
@else
    {{-- Berkas logo belum ada: tulisan tetap tampil supaya kepala halaman
         tidak pernah kosong sama sekali. --}}
    <span {{ $attributes->merge(['class' => "logo-brand-teks {$kelasVarian}"]) }}>Record</span>
@endif

@once
{{-- Gaya ditanam langsung di sini, bukan lewat @push('styles').
     Komponen ini dipakai di dalam layout (navbar & footer), yang dirender
     SETELAH bagian <head> selesai — dorongan ke stack di head tidak akan
     pernah sampai. @once menjaga blok ini hanya keluar sekali per halaman. --}}
<style>
    /* ══════════ Logo brand ══════════
       Ditulis di sini, bukan di resources/css/app.css, supaya cahayanya
       langsung berlaku tanpa menunggu `npm run build`. Aturan di app.css
       hanya aktif setelah CSS-nya dibangun ulang, dan itu mudah terlupa. */

    .logo-brand {
        position: relative;
        display: inline-flex;
        align-items: center;
        line-height: 0;
        /* Ruang untuk cahaya di sekeliling logo supaya tidak terpotong */
        padding: 4px;
    }

    /*
     * Ukurannya ditentukan LEBARNYA, bukan tingginya.
     *
     * Logo ini berbentuk tulisan memanjang (rasio sekitar 7,8 : 1). Mengatur
     * tingginya membuat lebarnya meledak tak terduga dan mendesak menu di
     * sebelahnya; mengatur lebarnya jauh lebih bisa diramalkan.
     */
    .logo-brand-gambar {
        width: var(--lebar-logo, 200px);
        height: auto;
        object-fit: contain;
        display: block;
        transition: transform 420ms cubic-bezier(.2, .8, .3, 1);
        will-change: filter, transform;
    }

    /*
     * clamp() membuat logonya ikut mengecil di layar sempit tanpa perlu
     * titik henti tambahan — di layar lebar ia besar, di ponsel tidak
     * sampai mendesak tombol menu.
     */
    .logo-brand-kecil  { --lebar-logo: clamp(112px, 13vw, 140px); }
    .logo-brand-sedang { --lebar-logo: clamp(126px, 14vw, 168px); }
    .logo-brand-besar  { --lebar-logo: clamp(180px, 22vw, 260px); }

    /*
     * Tidak ada bayangan berwarna di sekeliling logo.
     *
     * Cahaya biru sebelumnya membuat logo tampak seperti ditempel di atas
     * kartu bercahaya dan mengotori latar putih navbar. Kesan hidup cukup
     * datang dari kilau yang menyapu di bawah ini.
     */

    /*
     * ── Logo di latar gelap ──
     *
     * Tulisan logonya biru tua, dan footer juga biru tua — tanpa perlakuan
     * apa pun, logonya nyaris tak terlihat. Yang diberi alas terang di sini
     * adalah LATARNYA, bukan logonya: warna logo tetap utuh apa adanya,
     * hanya diberi bidang putih agar terbaca.
     *
     * Putihnya sengaja tidak murni (#F7F9FC) supaya tidak menyilaukan di
     * tengah footer gelap, dan sudutnya dibulatkan sedikit agar terbaca
     * sebagai bidang yang disengaja, bukan gambar yang latarnya lupa
     * dihapus.
     */
    .logo-brand-gelap {
        padding: 10px 16px;
        border-radius: 10px;
        background: #F7F9FC;
        box-shadow: 0 2px 10px rgb(0 0 0 / .18);
    }

    /*
     * ── Kilau menyapu ──
     *
     * Lapisan cahaya digambar di atas logo, lalu DITOPENG memakai berkas logo
     * itu sendiri. Tanpa topeng, kilaunya akan melintasi kotak persegi di
     * sekeliling logo dan terlihat seperti kaca yang disorot — bukan huruf
     * yang berkilau.
     */
    .logo-brand-kilau {
        position: absolute;
        inset: 4px;                 /* sama dengan padding wadahnya */
        pointer-events: none;

        background: linear-gradient(100deg,
            transparent 42%,
            rgb(255 255 255 / .1) 47%,
            rgb(255 255 255 / .85) 50%,
            rgb(255 255 255 / .1) 53%,
            transparent 58%);
        background-size: 260% 100%;
        background-repeat: no-repeat;

        -webkit-mask-image: var(--berkas-logo);
                mask-image: var(--berkas-logo);
        -webkit-mask-size: contain;
                mask-size: contain;
        -webkit-mask-repeat: no-repeat;
                mask-repeat: no-repeat;
        -webkit-mask-position: center;
                mask-position: center;

        animation: logo-sapu 9s ease-in-out infinite;
    }

    /* Kilaunya mengikuti alas, bukan tepi luar kotaknya. */
    .logo-brand-gelap .logo-brand-kilau { inset: 10px 16px; }

    /*
     * Menyapu sekali, lalu diam agak lama sebelum mengulang.
     *
     * Dua hal yang diatur di sini, dan keduanya berbeda:
     *   - lamanya satu putaran (9 detik) menentukan seberapa SERING menyapu,
     *   - jarak antara 6% dan 42% menentukan seberapa PELAN sapuannya.
     *
     * Sapuannya sendiri berjalan sekitar 3,2 detik — hampir dua kali lebih
     * pelan daripada sebelumnya — lalu logonya diam sekitar 4,7 detik.
     */
    @keyframes logo-sapu {
        0%, 6%    { background-position: 190% 0; opacity: 0; }
        10%       { opacity: 1; }
        42%       { background-position: -80% 0; opacity: 1; }
        48%, 100% { background-position: -80% 0; opacity: 0; }
    }

    /* Disorot: menyapu lagi lebih cepat, tapi tetap tidak terburu-buru. */
    .logo-brand:hover .logo-brand-kilau {
        animation-duration: 3.2s;
    }

    /* Disentuh: logonya membesar sedikit. */
    .logo-brand:hover .logo-brand-gambar {
        transform: scale(1.04);
    }

    /* ── Cadangan tulisan bila berkas logo belum ada ── */
    .logo-brand-teks {
        display: inline-block;
        font-size: 30px;
        font-weight: 900;
        letter-spacing: -.02em;
        text-transform: uppercase;
        background-size: 200% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: logo-kilau 8s linear infinite;
    }
    .logo-brand-teks.logo-brand-terang {
        background-image: linear-gradient(to right,
            #1B3A6B 20%, #4B85D6 40%, #A2C4F6 50%, #4B85D6 60%, #1B3A6B 80%);
    }
    .logo-brand-teks.logo-brand-gelap {
        background-image: linear-gradient(to right,
            #fff 20%, #93C5FD 40%, #DBEAFE 50%, #93C5FD 60%, #fff 80%);
    }
    @keyframes logo-kilau {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }

    /* Gerakan terus-menerus bisa memicu pusing bagi sebagian orang. */
    @media (prefers-reduced-motion: reduce) {
        .logo-brand-gambar, .logo-brand-teks { animation: none; }
        .logo-brand-kilau { display: none; }
        .logo-brand:hover .logo-brand-gambar { transform: none; }

    }
</style>
@endonce
