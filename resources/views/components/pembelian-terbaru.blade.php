@props(['pembelian' => []])

{{-- Notifikasi pembelian terbaru.
     Isinya pesanan yang benar-benar terjadi (lihat PembelianTerbaruService).
     Bila belum ada satu pun, seluruh komponen tidak ikut dirender — lebih
     baik diam daripada menampilkan contoh yang tidak pernah terjadi. --}}
@if(! empty($pembelian))
<div x-data="pembelianTerbaru({{ Js::from($pembelian) }})"
     x-show="tampil"
     x-cloak
     x-transition:enter="transition ease-out duration-500 transform"
     x-transition:enter-start="-translate-y-12 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-400 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
     class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full pointer-events-auto">

    <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-2xl p-4 shadow-2xl flex items-center gap-3.5 relative overflow-hidden">
        {{-- Bilah waktu tayang --}}
        <div class="absolute bottom-0 left-0 h-1 bg-accent transition-all duration-100 ease-linear"
             :style="`width: ${sisaWaktu}%`"></div>

        {{-- Gambar produk --}}
        <a :href="tautanProduk" class="shrink-0 relative">
            <template x-if="kini && kini.gambar">
                <img :src="alamatGambar(kini.gambar)" :alt="kini.produk"
                     class="w-14 h-14 rounded-xl object-cover border border-gray-100 shadow-sm">
            </template>
            <template x-if="!kini || !kini.gambar">
                <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-lg">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </template>
            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">
                <i class="fa-solid fa-check"></i>
            </span>
        </a>

        {{-- Keterangan --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1 mb-0.5">
                <p class="text-xs font-bold text-gray-900 truncate">
                    <span class="text-primary font-black" x-text="kini ? kini.nama : ''"></span>
                    <span class="text-gray-500 font-normal">telah membeli</span>
                </p>
                <span class="text-[10px] text-gray-400 shrink-0" x-text="lamaBerlalu"></span>
            </div>

            <a :href="tautanProduk"
               class="block text-xs font-bold text-gray-800 truncate leading-snug hover:text-primary transition"
               x-text="kini ? kini.produk : ''"></a>

            <p class="text-xs font-black text-accent mt-0.5" x-text="nominalTampil"></p>
        </div>

        <button @click="sembunyikan(true)" aria-label="Tutup notifikasi"
                class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1 text-xs">
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
            lamaBerlalu: '',
            nominalTampil: '',
            tautanProduk: '#',

            pewaktu: null,
            pewaktuBilah: null,
            dihentikan: false,

            DURASI_TAYANG: 6000,
            JEDA_MIN: 60000,    // 1 menit
            JEDA_MAKS: 180000,  // 3 menit

            init() {
                if (this.daftar.length === 0) return;

                this.acakUrutan();

                // Muncul sekali di awal supaya pengunjung baru langsung
                // melihat bahwa tokonya hidup.
                this.pewaktu = setTimeout(() => this.tayangkan(), 4000);
            },

            destroy() {
                clearTimeout(this.pewaktu);
                clearInterval(this.pewaktuBilah);
            },

            /* Urutan diacak sekali, lalu dijalani berurutan. Memilih acak tiap
               kali membuat entri yang sama bisa muncul dua kali beruntun. */
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

                if (this.posisi >= this.urutan.length) this.acakUrutan();

                this.kini          = this.daftar[this.urutan[this.posisi++]];
                this.lamaBerlalu   = this.hitungLamaBerlalu(this.kini.waktu);
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

            /* Ditutup sendiri: jadwalkan yang berikutnya.
               Ditutup pembeli: berhenti sampai halaman dimuat ulang —
               menutup notifikasi berarti tidak mau diganggu. */
            sembunyikan(olehPengunjung = false) {
                this.tampil = false;
                clearInterval(this.pewaktuBilah);
                clearTimeout(this.pewaktu);

                if (olehPengunjung) {
                    this.dihentikan = true;
                    return;
                }

                const jeda = this.JEDA_MIN + Math.random() * (this.JEDA_MAKS - this.JEDA_MIN);
                this.pewaktu = setTimeout(() => this.tayangkan(), jeda);
            },

            /* Dihitung di browser dari waktu asli pesanan, jadi halaman yang
               lama dibiarkan terbuka tidak menampilkan keterangan basi. */
            hitungLamaBerlalu(waktu) {
                const menit = Math.floor((Date.now() - new Date(waktu).getTime()) / 60000);

                if (menit < 1)    return 'Baru saja';
                if (menit < 60)   return `${menit} menit lalu`;

                const jam = Math.floor(menit / 60);
                if (jam < 24)     return `${jam} jam lalu`;

                const hari = Math.floor(jam / 24);
                return hari === 1 ? 'Kemarin' : `${hari} hari lalu`;
            },

            formatRupiah(nilai) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(nilai));
            },

            alamatGambar(berkas) {
                if (! berkas) return '';
                return berkas.startsWith('http') ? berkas : '/storage/' + berkas;
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
