@props(['pembelian' => []])

{{-- Notifikasi pembelian terbaru (Social Proof) di pojok kiri bawah --}}
@if(! empty($pembelian))
<div x-data="pembelianTerbaru({{ Js::from($pembelian) }})"
     x-show="tampil"
     x-cloak
     x-transition:enter="transition ease-out duration-500 transform"
     x-transition:enter-start="-translate-x-12 translate-y-6 opacity-0 scale-95"
     x-transition:enter-end="translate-x-0 translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-400 transform"
     x-transition:leave-start="translate-x-0 translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-x-12 opacity-0 scale-95"
     class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 z-50 max-w-[320px] sm:max-w-sm w-full pointer-events-auto">

    <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-2xl p-3.5 sm:p-4 shadow-2xl flex items-center gap-3 relative overflow-hidden ring-1 ring-black/5">
        {{-- Bilah waktu tayang meluncur --}}
        <div class="absolute bottom-0 left-0 h-1 bg-orange-500 transition-all duration-100 ease-linear"
             :style="`width: ${sisaWaktu}%`"></div>

        {{-- Gambar produk --}}
        <a :href="tautanProduk" class="shrink-0 relative group">
            <template x-if="kini && kini.gambar">
                <img :src="alamatGambar(kini.gambar)" :alt="kini.produk"
                     class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover border border-gray-100 shadow-sm group-hover:scale-105 transition duration-300">
            </template>
            <template x-if="!kini || !kini.gambar">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </template>
            <span class="absolute -bottom-1 -right-1 w-4 h-4 sm:w-5 sm:h-5 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[9px] sm:text-[10px] shadow-sm ring-2 ring-white">
                <i class="fa-solid fa-check"></i>
            </span>
        </a>

        {{-- Keterangan Pembeli & Produk --}}
        <div class="flex-1 min-w-0 pr-2">
            <div class="flex items-center justify-between gap-1 mb-0.5">
                <p class="text-xs font-bold text-gray-900 truncate">
                    <span class="text-blue-900 font-extrabold" x-text="kini ? kini.nama : ''"></span>
                    <span class="text-gray-400 text-[10px] font-normal" x-show="kini && kini.kota" x-text="'(' + kini.kota + ')'"></span>
                    <span class="text-gray-500 font-medium text-[11px]">membeli</span>
                </p>
                <span class="text-[10px] text-gray-400 font-medium shrink-0" x-text="waktuTampil"></span>
            </div>

            <a :href="tautanProduk"
               class="block text-xs font-bold text-gray-800 truncate leading-snug hover:text-orange-600 transition"
               x-text="kini ? kini.produk : ''"></a>

            <p class="text-xs font-black text-orange-600 mt-0.5" x-text="nominalTampil"></p>
        </div>

        {{-- Tombol Tutup --}}
        <button @click="sembunyikan(true)" aria-label="Tutup notifikasi"
                class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1 text-xs -mt-5">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
(function () {
    function pasangKomponen() {
        if (typeof Alpine === 'undefined') return;

        Alpine.data('pembelianTerbaru', (daftar) => ({
            daftar: daftar || [],
            urutan: [],
            posisi: 0,

            tampil: false,
            kini: null,
            sisaWaktu: 100,
            waktuTampil: '',
            nominalTampil: '',
            tautanProduk: '#',

            pewaktu: null,
            pewaktuBilah: null,
            dihentikan: false,

            DURASI_TAYANG: 5000, // Tayang 5 detik

            init() {
                if (this.daftar.length === 0) return;

                this.acakUrutan();

                // Muncul pertama kali 5 detik setelah halaman selesai dibuka
                this.pewaktu = setTimeout(() => this.tayangkan(), 5000);
            },

            destroy() {
                clearTimeout(this.pewaktu);
                clearInterval(this.pewaktuBilah);
            },

            acakUrutan() {
                this.urutan = [...this.daftar.keys()];
                for (let i = this.urutan.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [this.urutan[i], this.urutan[j]] = [this.urutan[j], this.urutan[i]];
                }
                this.posisi = 0;
            },

            tayangkan() {
                if (this.dihentikan || this.daftar.length === 0) return;

                if (this.posisi >= this.urutan.length) {
                    this.acakUrutan();
                }

                this.kini = this.daftar[this.urutan[this.posisi++]];
                
                // Format waktu segar dinamis (TIDAK ADA HARI)
                const menitAcak = (this.kini.menit || Math.floor(Math.random() * 35) + 1);
                if (menitAcak <= 2) {
                    this.waktuTampil = 'Baru saja';
                } else {
                    this.waktuTampil = `${menitAcak} mnt lalu`;
                }

                this.nominalTampil = this.formatRupiah(this.kini.nominal);
                this.tautanProduk  = this.kini.slug ? `/products/${this.kini.slug}` : '#';

                this.tampil    = true;
                this.sisaWaktu = 100;

                const langkah = 50;
                let berjalan = 0;

                clearInterval(this.pewaktuBilah);
                this.pewaktuBilah = setInterval(() => {
                    berjalan += langkah;
                    this.sisaWaktu = Math.max(0, 100 - (berjalan / this.DURASI_TAYANG) * 100);
                    if (berjalan >= this.DURASI_TAYANG) clearInterval(this.pewaktuBilah);
                }, langkah);

                clearTimeout(this.pewaktu);
                this.pewaktu = setTimeout(() => this.sembunyikan(), this.DURASI_TAYANG);
            },

            sembunyikan(olehPengunjung = false) {
                this.tampil = false;
                clearInterval(this.pewaktuBilah);
                clearTimeout(this.pewaktu);

                if (olehPengunjung) {
                    this.dihentikan = true;
                    return;
                }

                // Hitung jeda acak alami bervariasi agar seolah-olah real
                const jedaAcak = this.hitungJedaAcak();
                this.pewaktu = setTimeout(() => this.tayangkan(), jedaAcak);
            },

            // Pola Jeda Acak Realistis:
            // - Jika ada pesanan real asli: jeda cepat 5-10 detik
            // - Acak alami: kadang 20-40 detik, kadang 1-2 menit, kadang 3-5 menit
            hitungJedaAcak() {
                // Cek item antrean berikutnya
                const indeksBerikutnya = this.urutan[this.posisi % this.urutan.length];
                const itemBerikutnya = this.daftar[indeksBerikutnya];

                // Jika pesanan real dari pembeli asli: jeda cepat (5 - 8 detik)
                if (itemBerikutnya && itemBerikutnya.asli) {
                    return Math.floor(Math.random() * (8000 - 5000 + 1)) + 5000;
                }

                const acak = Math.random();

                if (acak < 0.40) {
                    // 40% jeda cepat/sedang (18 detik s.d 40 detik)
                    return Math.floor(Math.random() * (40000 - 18000 + 1)) + 18000;
                } else if (acak < 0.75) {
                    // 35% jeda 1 s.d 2 menit (60 detik s.d 120 detik)
                    return Math.floor(Math.random() * (120000 - 60000 + 1)) + 60000;
                } else {
                    // 25% jeda santai 2.5 s.d 4.5 menit (150 detik s.d 270 detik)
                    return Math.floor(Math.random() * (270000 - 150000 + 1)) + 150000;
                }
            },

            formatRupiah(nilai) {
                if (!nilai || isNaN(nilai)) return '';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(nilai));
            },

            alamatGambar(berkas) {
                if (!berkas) return '';
                if (berkas.startsWith('http')) return berkas;
                return 'https://admin.recordshoes.com/storage/' + berkas.replace(/^\/+/, '');
            },
        }));
    }

    if (window.Alpine) {
        pasangKomponen();
    } else {
        document.addEventListener('alpine:init', pasangKomponen);
    }
})();
</script>
@endif