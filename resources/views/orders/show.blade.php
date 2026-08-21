<x-app-layout>
    <x-slot name="title">Detail & Tracking Pesanan #{{ $order->order_number }}</x-slot>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 text-text">

        {{-- Navigation & Title --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-text-light hover:text-primary transition inline-flex items-center gap-1.5 mb-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat Pesanan
                </a>
                <h1 class="text-2xl font-black text-primary uppercase tracking-wide">
                    Pesanan <span class="text-accent font-mono">#{{ $order->order_number }}</span>
                </h1>
                <p class="text-xs text-text-light mt-1">Dipesan pada {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 rounded-sm text-xs font-bold uppercase tracking-wider
                    @if($order->status === 'pending') bg-amber-50 text-amber-800 border border-amber-200
                    @elseif($order->status === 'processing') bg-sky-50 text-sky-800 border border-sky-200
                    @elseif($order->status === 'shipped') bg-indigo-50 text-indigo-800 border border-indigo-200
                    @elseif($order->status === 'completed') bg-emerald-50 text-emerald-800 border border-emerald-200
                    @else bg-rose-50 text-rose-800 border border-rose-200
                    @endif">
                    <i class="fa-solid fa-circle text-[8px] mr-1.5"></i> {{ $order->status_label }}
                </span>
            </div>
        </div>

        {{-- Galat validasi. --}}
        @if($errors->any())
            <div class="galat-ringkas">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <p class="galat-ringkas-judul">Pengajuanmu belum terkirim</p>
                    <ul class="galat-ringkas-daftar">
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                    <p class="galat-ringkas-bantu">
                        Perbaiki dulu, lalu kirim ulang. Isian yang sudah kamu tulis masih tersimpan.
                    </p>
                </div>
            </div>
        @endif

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-sm bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- VISUAL ORDER TRACKING TIMELINE --}}
        @php
            $steps = [
                'pending'    => ['step' => 1, 'title' => 'Pesanan Dibuat', 'desc' => 'Menunggu Pembayaran', 'icon' => 'fa-file-invoice'],
                'processing' => ['step' => 2, 'title' => 'Diproses', 'desc' => 'Penjual menyiapkan barang', 'icon' => 'fa-box-open'],
                'shipped'    => ['step' => 3, 'title' => 'Dikirim', 'desc' => 'Paket dalam perjalanan', 'icon' => 'fa-truck-fast'],
                'completed'  => ['step' => 4, 'title' => 'Selesai', 'desc' => 'Pesanan diterima pembeli', 'icon' => 'fa-house-circle-check'],
            ];

            $currentStepIndex = 1;
            if ($order->status === 'processing') $currentStepIndex = 2;
            elseif ($order->status === 'shipped') $currentStepIndex = 3;
            elseif ($order->status === 'completed') $currentStepIndex = 4;
            elseif ($order->status === 'cancelled') $currentStepIndex = 0;
        @endphp

        <div class="bg-white border border-border rounded-sm p-6 sm:p-8 shadow-sm space-y-6">
            <h2 class="text-xs font-black text-primary uppercase tracking-wider border-b border-border pb-3 flex items-center gap-2">
                <i class="fa-solid fa-route text-accent"></i>
                <span>Status & Pelacakan Pengiriman</span>
            </h2>

            @if($order->status === 'cancelled')
                <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-sm p-6 text-center space-y-2">
                    <i class="fa-solid fa-circle-xmark text-3xl text-rose-500 block"></i>
                    <h3 class="font-bold text-sm">Pesanan Dibatalkan</h3>

                    @if($order->cancellation_reason)
                        {{-- Alasan yang dipilih pembeli saat membatalkan --}}
                        <div class="bg-white/70 border border-rose-200 rounded-sm p-3 text-left inline-block max-w-md">
                            <p class="text-[10px] font-black uppercase tracking-wider text-rose-500">Alasan Pembatalan</p>
                            <p class="text-xs font-bold text-rose-900 mt-1">{{ $order->cancellation_reason }}</p>
                            @if($order->cancellation_note)
                                <p class="text-xs text-rose-700 mt-1.5 leading-relaxed">"{{ $order->cancellation_note }}"</p>
                            @endif
                            @if($order->cancelled_at)
                                <p class="text-[10px] text-rose-400 mt-2">
                                    Dibatalkan {{ $order->cancelled_at->translatedFormat('d F Y, H:i') }} WIB
                                </p>
                            @endif
                        </div>
                    @endif

                    <p class="text-xs text-rose-700">Pesanan ini telah dibatalkan. Jika Anda membutuhkan bantuan, silakan hubungi Customer Service kami.</p>
                </div>
            @else
                {{-- Progress Bar Container --}}
                <div class="relative py-4">
                    {{-- Connecting Line --}}
                    <div class="hidden sm:block absolute top-1/2 left-8 right-8 h-1 bg-gray-100 -translate-y-1/2 rounded-full">
                        <div class="h-full bg-primary transition-all duration-500 rounded-full"
                             style="width: {{ (($currentStepIndex - 1) / 3) * 100 }}%"></div>
                    </div>

                    {{-- Step Nodes --}}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative z-10">
                        @foreach($steps as $key => $info)
                            @php
                                $isDone = $currentStepIndex >= $info['step'];
                                $isCurrent = $currentStepIndex === $info['step'];
                            @endphp
                            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-2">
                                <div class="h-12 w-12 rounded-sm flex items-center justify-center font-bold text-base transition-all shadow-sm shrink-0
                                    @if($isDone) bg-primary text-white @else bg-gray-100 text-text-light @endif
                                    @if($isCurrent) ring-4 ring-primary/20 scale-105 @endif">
                                    <i class="fa-solid {{ $info['icon'] }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold @if($isDone) text-primary @else text-text-light @endif">
                                        {{ $info['title'] }}
                                    </h4>
                                    <p class="text-[10px] text-text-light mt-0.5">{{ $info['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tracking Number Box & Live Tracking Timeline Widget --}}
                @if($order->tracking_number)
                    @php
                        $courierSlug = strtolower(preg_replace('/[^a-zA-Z]/', '', $order->courier ?? ''));
                        $trackingUrl = match(true) {
                            str_contains($courierSlug, 'jne')      => 'https://www.jne.co.id/id/tracking/trace?awb=' . $order->tracking_number,
                            str_contains($courierSlug, 'jt')       => 'https://jet.id/tracking?awb=' . $order->tracking_number,
                            str_contains($courierSlug, 'sicepat')  => 'https://www.sicepat.com/checkAwb?awb=' . $order->tracking_number,
                            str_contains($courierSlug, 'anteraja') => 'https://anteraja.id/tracking/' . $order->tracking_number,
                            str_contains($courierSlug, 'pos')      => 'https://www.posindonesia.co.id/id/tracking?awb=' . $order->tracking_number,
                            str_contains($courierSlug, 'gosend')   => 'https://driver.gojek.com/track/' . $order->tracking_number,
                            str_contains($courierSlug, 'grab')     => 'https://web.grab.com/sg/delivery-tracking/#' . $order->tracking_number,
                            default                                 => 'https://cekresi.com/?noresi=' . $order->tracking_number,
                        };
                    @endphp
                    <div class="bg-primary text-white rounded-t-xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm border border-primary-dark">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-accent-light uppercase tracking-widest block">Nomor Resi Pengiriman (Biteship)</span>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-black font-mono select-all tracking-wider text-white">{{ $order->tracking_number }}</span>
                                <span class="bg-white/10 text-xs px-2.5 py-0.5 rounded-sm font-bold text-white">{{ $order->courier }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); this.textContent='✓ Disalin';"
                                    class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2 rounded-lg transition flex items-center gap-1.5 border border-white/20">
                                <i class="fa-regular fa-copy"></i> Salin Resi
                            </button>
                            <a href="{{ $trackingUrl }}" target="_blank" rel="noopener noreferrer"
                               class="bg-accent hover:bg-accent-dark text-white text-xs font-bold px-4 py-2 rounded-lg transition flex items-center gap-1.5 shadow">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Cek Web Kurir
                            </a>
                        </div>
                    </div>

                    {{-- ===== WIDGET LIVE TRACKING ALA SHOPEE (BITESHIP REAL-TIME) ===== --}}
                    <div x-data="liveTracker('{{ route('orders.tracking-status', $order->order_number) }}')" x-init="fetchTracking()"
                         class="bg-white border-x border-b border-gray-200 rounded-b-xl p-5 shadow-sm space-y-4">

                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2 text-gray-800 font-bold text-xs uppercase tracking-wide">
                                <i class="fa-solid fa-location-dot text-rose-500 text-sm animate-bounce"></i>
                                <span>Lacak Posisi Paket Real-Time (Shopee Style)</span>
                            </div>
                            <button type="button" @click="fetchTracking()" :disabled="loading"
                                    class="text-[11px] font-bold text-primary hover:text-accent flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-rotate text-xs" :class="loading ? 'animate-spin' : ''"></i>
                                <span x-text="loading ? 'Memuat...' : 'Perbarui Status'"></span>
                            </button>
                        </div>

                        {{-- State Loading --}}
                        <div x-show="loading" class="py-8 text-center text-gray-400 space-y-2">
                            <i class="fa-solid fa-circle-notch animate-spin text-2xl text-accent"></i>
                            <p class="text-xs">Menghubungkan ke API Biteship & Kurir {{ $order->courier }}...</p>
                        </div>

                        {{-- State Timeline Tracking --}}
                        <div x-show="!loading && history.length > 0" class="space-y-4">
                            {{-- Headline Status Terbaru --}}
                            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-sm">
                                    <i class="fa-solid fa-truck-fast"></i>
                                </div>
                                <div class="space-y-0.5 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] uppercase font-bold text-emerald-800 tracking-wider">Status Terkini</span>
                                        <span x-text="history[0]?.time ? new Date(history[0].time).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : ''" class="text-[10px] text-emerald-600 font-medium"></span>
                                    </div>
                                    <p x-text="history[0]?.description" class="text-xs font-bold text-emerald-900 leading-snug"></p>
                                    <template x-if="history[0]?.location">
                                        <p class="text-[11px] text-emerald-700 font-medium">
                                            <i class="fa-solid fa-map-pin mr-1"></i><span x-text="history[0].location"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            {{-- Timeline Rinci (Shopee Vertical Line) --}}
                            <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
                                <template x-for="(item, idx) in history" :key="idx">
                                    <div class="relative flex flex-col gap-0.5 text-xs">
                                        {{-- Dot Indicator --}}
                                        <div class="absolute -left-6 top-1 w-3 h-3 rounded-full border-2 transition-all shadow-xs"
                                             :class="idx === 0 ? 'bg-emerald-500 border-white ring-2 ring-emerald-200 scale-110' : 'bg-gray-300 border-white'"></div>

                                        <div class="flex items-center justify-between gap-2">
                                            <span x-text="item.description" :class="idx === 0 ? 'font-bold text-gray-900' : 'text-gray-600'"></span>
                                            <span x-text="item.time ? new Date(item.time).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : ''" class="text-[10px] text-gray-400 shrink-0 font-mono"></span>
                                        </div>

                                        <template x-if="item.location">
                                            <span class="text-[10px] text-gray-400 font-medium">
                                                Lokasi: <span x-text="item.location"></span>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Kosong / Error Fallback --}}
                        <div x-show="!loading && history.length === 0" class="py-6 text-center text-gray-500 text-xs">
                            <i class="fa-solid fa-box-open text-gray-300 text-3xl mb-2 block"></i>
                            <span>Informasi tracking sedang disinkronkan oleh sistem Biteship. Silakan klik tombol 'Perbarui Status' di atas.</span>
                        </div>
                    </div>

                    @push('scripts')
                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.data('liveTracker', (endpoint) => ({
                                endpoint: endpoint,
                                loading: true,
                                history: [],
                                async fetchTracking() {
                                    this.loading = true;
                                    try {
                                        const res = await fetch(this.endpoint);
                                        const data = await res.json();
                                        if (data.success && data.history) {
                                            this.history = data.history;
                                        }
                                    } catch (e) {
                                        console.error('Failed to load live tracking:', e);
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }));
                        });
                    </script>
                    @endpush
                @elseif($order->status === 'shipped')
                    <div class="bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-xl p-4 text-xs flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-ramp-box text-indigo-600 text-xl"></i>
                            <span>Paket Anda sedang dalam proses pengiriman oleh kurir <strong>{{ $order->courier }}</strong>. Nomor resi otomatis BiteShip sedang diterbitkan.</span>
                        </div>
                    </div>
                @endif

                {{-- ══ Pembatalan ══ --}}
                @php
                    $pembatalan = app(\App\Services\PembatalanPesananService::class);
                    $jalurBatal = $pembatalan->jalur($order);
                    $L = \App\Services\PembatalanPesananService::class;
                @endphp

                @if($jalurBatal !== $L::TIDAK_BISA && $jalurBatal !== $L::LEWAT_PENGEMBALIAN)
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        @if($order->payment_status === 'unpaid')
                            <a href="{{ route('checkout.payment', $order->order_number) }}"
                               class="flex-1 bg-accent hover:bg-accent-dark text-white font-bold text-xs py-3.5 rounded-sm transition uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm">
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Bayar Sekarang (Midtrans)</span>
                            </a>
                        @endif

                        <button type="button"
                                onclick="window.dispatchEvent(new CustomEvent('buka-batal'))"
                                class="{{ $order->payment_status === 'unpaid' ? 'w-full sm:w-auto' : 'w-full' }} bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs py-3.5 px-5 rounded-sm transition uppercase tracking-wider flex items-center justify-center gap-1.5 border border-rose-200">
                            <i class="fa-solid fa-xmark"></i>
                            <span>Batalkan Pesanan</span>
                        </button>
                    </div>

                    {{-- Akibatnya ditulis di depan, sebelum tombolnya ditekan --}}
                    <p class="batal-keterangan">
                        @if($jalurBatal === $L::LANGSUNG_REFUND)
                            <i class="fa-solid fa-wallet"></i>
                            Pesanan belum diatur pengirimannya, jadi bisa langsung dibatalkan.
                            Dana <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                            akan kembali ke saldo R_Pay-mu seketika.
                        @else
                            <i class="fa-solid fa-circle-info"></i>
                            Pesanan belum dibayar, jadi bisa dibatalkan kapan saja tanpa biaya.
                        @endif
                    </p>
                @endif

                {{-- ══ Setelah barang dikirim admin: Konfirmasi & Pengembalian ══ --}}
                @php
                    $pengembalian  = $order->returns->firstWhere('type', 'return');
                    $bolehRetur    = \App\Http\Controllers\ReturnController::bolehMengajukan($order);
                @endphp

                @if(! $pengembalian && $order->status === 'shipped')
                    {{-- Box panduan tindakan saat paket sedang dikirim --}}
                    <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-5 space-y-4 shadow-sm">
                        {{-- Header --}}
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-lg">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-900">Paket Sedang Dalam Perjalanan</h4>
                                <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">
                                    Setelah paket Anda tiba, klik <strong>"Konfirmasi Barang Diterima"</strong>.<br>
                                    Jika ada masalah, ajukan pengembalian <strong>sebelum</strong> mengkonfirmasi penerimaan.
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Konfirmasi Penerimaan --}}
                        <form id="form-confirm-{{ $order->order_number }}"
                              action="{{ route('orders.confirm', $order->order_number) }}" method="POST">
                            @csrf
                            <button type="button"
                                    onclick="document.getElementById('modal-confirm-{{ $order->order_number }}').classList.remove('hidden')"
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs py-3.5 rounded-lg transition uppercase tracking-wider flex items-center justify-center gap-2 shadow-md">
                                <i class="fa-solid fa-circle-check text-base"></i>
                                <span>Konfirmasi Barang Sudah Diterima</span>
                            </button>
                        </form>

                        {{-- Modal konfirmasi penerimaan. --}}
                        <div id="modal-confirm-{{ $order->order_number }}"
                             class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6">
                            <div class="terima-kotak">

                                <div class="terima-kepala">
                                    <div class="terima-ikon"><i class="fa-solid fa-box-open"></i></div>
                                    <h3 class="terima-judul">Konfirmasi Penerimaan Barang</h3>
                                    <p class="terima-sub">
                                        Pastikan paket sudah kamu terima dalam kondisi baik dan sesuai pesanan.
                                        Tindakan ini tidak bisa dibatalkan.
                                    </p>
                                </div>

                                <div class="terima-isi">
                                    {{-- Syarat garansi --}}
                                    <div class="terima-garansi">
                                        <p class="terima-garansi-judul">
                                            <i class="fa-solid fa-video"></i>
                                            Syarat Klaim Garansi
                                        </p>
                                        <p class="terima-garansi-teks">
                                            Klaim garansi hanya kami layani bila disertai
                                            <strong>video unboxing asli tanpa potongan</strong> —
                                            direkam sekali jalan sejak paket masih tersegel sampai barang keluar
                                            seluruhnya. Kirimkan videonya ke kami saat mengajukan klaim.
                                        </p>
                                        <ul class="terima-garansi-daftar">
                                            <li><i class="fa-solid fa-check"></i> Rekam sekali jalan, tanpa jeda dan tanpa dipotong</li>
                                            <li><i class="fa-solid fa-check"></i> Segel dan label pengiriman terlihat jelas di awal</li>
                                            <li><i class="fa-solid fa-check"></i> Seluruh isi paket terlihat sampai dikeluarkan</li>
                                        </ul>
                                        <p class="terima-garansi-tegas">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            Tanpa video tersebut, <strong>garansi tidak berlaku</strong>.
                                        </p>
                                    </div>

                                    {{-- Pengembalian tetap terbuka setelah ini --}}
                                    <div class="terima-catatan">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <p>
                                            Pengajuan pengembalian tetap bisa kamu lakukan sampai
                                            <strong>{{ config('alasan-retur.batas_hari', 7) }} hari</strong>
                                            setelah pesanan ditandai selesai.
                                        </p>
                                    </div>
                                </div>

                                <div class="terima-kaki">
                                    <button type="button"
                                            onclick="document.getElementById('modal-confirm-{{ $order->order_number }}').classList.add('hidden')"
                                            class="terima-tombol terima-tombol-batal">
                                        Belum, Cek Dulu
                                    </button>
                                    <button type="submit" form="form-confirm-{{ $order->order_number }}"
                                            class="terima-tombol terima-tombol-ya">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Ya, Sudah Diterima
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if($bolehRetur)
                            <div class="border-t border-emerald-100 pt-3 space-y-1.5">
                                <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('buka-retur'))"
                                        class="w-full bg-white border border-rose-200 hover:border-rose-400 hover:bg-rose-50 text-rose-700 font-bold text-xs py-3 rounded-lg transition uppercase tracking-wider flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Ada Masalah? Ajukan Pengembalian</span>
                                </button>
                                <p class="text-[10px] text-emerald-600 text-center leading-relaxed">
                                    Ajukan pengembalian jika barang rusak, tidak sesuai, atau salah ukuran — sebelum mengkonfirmasi penerimaan.
                                </p>
                            </div>
                        @endif
                    </div>

                @elseif($order->status === 'completed')
                    @php
                        $batasRetur = ($order->completed_at ?? $order->updated_at)
                            ->addDays(config('alasan-retur.batas_hari', 7));
                    @endphp

                    <div class="pt-2 space-y-4" id="nilai">
                        {{-- Ajakan menilai, menetap selama masih ada barang yang --}}
                        @if($belumDinilai->isNotEmpty())
                            <div class="nilai-ajakan">
                                <div class="nilai-ajakan-teks">
                                    <p class="nilai-ajakan-judul">
                                        <i class="fa-solid fa-star"></i>
                                        {{ $belumDinilai->count() === 1
                                            ? 'Produkmu belum dinilai'
                                            : $belumDinilai->count() . ' produkmu belum dinilai' }}
                                    </p>
                                    <p class="nilai-ajakan-ket">
                                        Beri bintang untuk membantu pembeli lain memilih.
                                        Komentar dan fotonya boleh dikosongkan.
                                    </p>
                                </div>
                                <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('buka-penilaian'))"
                                        class="nilai-ajakan-tombol">
                                    Beri Penilaian
                                </button>
                            </div>
                        @endif

                        {{-- Pengembalian TETAP terbuka setelah barang diterima, --}}
                        @if(! $pengembalian && $bolehRetur)
                            <div class="selesai-retur">
                                <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('buka-retur'))"
                                        class="selesai-retur-tombol">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Ada Masalah? Ajukan Pengembalian</span>
                                </button>
                                <p class="selesai-retur-ket">
                                    Masih bisa diajukan sampai
                                    <strong>{{ $batasRetur->translatedFormat('l, d F Y') }}</strong>.
                                    Sertakan video unboxing asli tanpa potongan saat mengajukan.
                                </p>
                            </div>
                        @elseif(! $pengembalian)
                            <div class="selesai-lewat">
                                <i class="fa-solid fa-clock"></i>
                                <p>
                                    Batas waktu pengajuan pengembalian sudah lewat
                                    ({{ $batasRetur->translatedFormat('d F Y') }}).
                                    Butuh bantuan? Hubungi Customer Service kami.
                                </p>
                            </div>
                        @endif

                        {{-- ══ Kode referal ══ --}}
                        @if($referalAktif)
                            <div class="referal-pesanan" x-data="{ tersalin: false }">
                                <div class="referal-pesanan-kepala">
                                    <div class="referal-pesanan-ikon"><i class="fa-solid fa-gift"></i></div>
                                    <div class="min-w-0">
                                        <h4 class="referal-pesanan-judul">Kode Referal Kamu Aktif</h4>
                                        <p class="referal-pesanan-sub">
                                            Pesanan ini sudah selesai, jadi kodemu sah dipakai teman.
                                        </p>
                                    </div>
                                </div>

                                <div class="referal-pesanan-kotak">
                                    <span class="referal-pesanan-kode">{{ $kodeReferal }}</span>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $kodeReferal }}');
                                                    tersalin = true; setTimeout(() => tersalin = false, 2000)"
                                            class="referal-pesanan-salin">
                                        <span x-show="!tersalin"><i class="fa-solid fa-copy"></i> Salin</span>
                                        <span x-show="tersalin" x-cloak><i class="fa-solid fa-check"></i> Tersalin</span>
                                    </button>
                                </div>

                                <div class="referal-pesanan-untung">
                                    <div>
                                        <p class="referal-pesanan-untung-nilai">{{ (int) config('referal.persen_diskon', 3) }}%</p>
                                        <p class="referal-pesanan-untung-label">Diskon buat temanmu</p>
                                    </div>
                                    <div>
                                        <p class="referal-pesanan-untung-nilai">{{ (int) config('referal.persen_komisi', 3) }}%</p>
                                        <p class="referal-pesanan-untung-label">Komisi buat kamu</p>
                                    </div>
                                </div>

                                {{-- Peringatan hangus, ditulis apa adanya --}}
                                <div class="referal-pesanan-ingat">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <p>
                                        Kode ini <strong>hangus</strong> bila pesananmu dibatalkan atau
                                        pengajuan pengembaliannya disetujui — dan pemakaian kodemu oleh
                                        teman jadi tidak berlaku. Kode akan aktif lagi setelah kamu
                                        punya pesanan lain yang selesai.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ══ Timeline pengembalian ══ --}}
                @if($pengembalian)
                    @php $alamatRetur = config('alasan-retur.alamat_pengembalian'); @endphp

                    <div class="pt-4 retur-jalur">
                        <div class="retur-jalur-kepala">
                            <div>
                                <h3 class="retur-jalur-judul">Pengajuan Pengembalian</h3>
                                {{-- Nomor pengajuan ditampilkan supaya pembeli punya --}}
                                @if($pengembalian->return_number)
                                    <p class="retur-jalur-nomor">{{ $pengembalian->return_number }}</p>
                                @endif
                                <p class="retur-jalur-sub">
                                    {{ $pengembalian->reason_label }} &middot; {{ $pengembalian->resolution_label }}
                                </p>
                            </div>
                            <span class="retur-lencana retur-lencana-{{ $pengembalian->status_color }}">
                                {{ $pengembalian->status_label }}
                            </span>
                        </div>

                        @if($pengembalian->ditolak)
                            {{-- Ditolak: timeline berhenti, alasannya ditulis apa adanya --}}
                            <div class="retur-tolak">
                                <p class="retur-tolak-judul">
                                    <i class="fa-solid fa-circle-xmark"></i> Pengajuan Ditolak
                                </p>
                                <p class="retur-tolak-isi">
                                    {{ $pengembalian->admin_notes ?: 'Pengajuanmu tidak dapat kami setujui.' }}
                                </p>
                                @if($pengembalian->inspection_notes)
                                <p class="retur-tolak-isi">
                                    <strong>Hasil pemeriksaan barang:</strong> {{ $pengembalian->inspection_notes }}
                                </p>
                                @endif
                                <p class="retur-tolak-bantu">
                                    Masih keberatan? Hubungi Customer Service kami dengan menyebut nomor pesanan
                                    {{ $order->order_number }}.
                                </p>
                            </div>
                        @else
                            <ol class="retur-langkah">
                                @foreach($pengembalian->timeline() as $langkah)
                                <li class="retur-langkah-item
                                    @if($langkah['selesai']) retur-langkah-selesai
                                    @elseif($langkah['sekarang']) retur-langkah-sekarang
                                    @else retur-langkah-menunggu @endif">
                                    <span class="retur-titik">
                                        <i class="fa-solid {{ $langkah['selesai'] ? 'fa-check' : $langkah['ikon'] }}"></i>
                                    </span>
                                    <div class="retur-langkah-teks">
                                        <p class="retur-langkah-judul">{{ $langkah['judul'] }}</p>
                                        <p class="retur-langkah-ket">{{ $langkah['keterangan'] }}</p>
                                        @if($langkah['waktu'])
                                        <p class="retur-langkah-waktu">
                                            {{ $langkah['waktu']->translatedFormat('d F Y, H:i') }} WIB
                                        </p>
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ol>
                        @endif

                        {{-- ── Giliran pembeli: kirim barangnya kembali ── --}}
                        @if($pengembalian->status === 'approved')
                            <div class="retur-kirim">
                                <p class="retur-kirim-judul">
                                    <i class="fa-solid fa-box-open"></i> Giliranmu: kirim barangnya ke alamat ini
                                </p>

                                <div class="retur-alamat">
                                    <p class="retur-alamat-nama">{{ $alamatRetur['nama'] }}</p>
                                    <p>{{ $alamatRetur['telepon'] }}</p>
                                    <p>
                                        {{ $alamatRetur['alamat'] }}, {{ $alamatRetur['kelurahan'] }},
                                        Kec. {{ $alamatRetur['kecamatan'] }}, {{ $alamatRetur['kota'] }},
                                        {{ $alamatRetur['provinsi'] }} {{ $alamatRetur['kode_pos'] }}
                                    </p>
                                </div>

                                <div class="retur-ongkir">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <p>
                                        <strong>Ongkos kirim balik ditanggung pembeli.</strong>
                                        Simpan bukti pengirimannya sampai proses ini selesai.
                                    </p>
                                </div>

                                <div class="retur-syarat">
                                    <p class="retur-syarat-judul">Barang akan kami periksa saat sampai</p>
                                    <p class="retur-syarat-ket">
                                        Kalau barangnya cacat, rusak, atau tidak sesuai dengan yang kamu ajukan,
                                        pengembalian dana akan <strong>ditolak</strong> dan barangnya kami kirim balik.
                                        Pastikan:
                                    </p>
                                    <ul class="retur-syarat-daftar">
                                        @foreach(config('alasan-retur.syarat_barang', []) as $syarat)
                                        <li><i class="fa-solid fa-check"></i> {{ $syarat }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                @if($pengembalian->approved_at && !$pengembalian->return_tracking_number)
                                <p class="retur-tenggat">
                                    Mohon paket disiapkan. Kurir akan menjemput barang paling lambat
                                    <strong>{{ $pengembalian->approved_at->addDays(config('alasan-retur.batas_kirim_balik_hari', 7))->translatedFormat('l, d F Y') }}</strong>.
                                </p>
                                @endif
                            </div>
                        @endif

                        {{-- ── Resi Retur Otomatis BiteShip ── --}}
                        @if($pengembalian->return_tracking_number)
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl space-y-3 mt-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-emerald-900 font-bold text-xs">
                                        <i class="fa-solid fa-truck-pickup text-emerald-600 text-sm"></i>
                                        <span>Penjemputan Retur Otomatis (BiteShip)</span>
                                    </div>
                                    <span class="bg-emerald-600 text-white font-extrabold text-[9px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                        Resi Terbit
                                    </span>
                                </div>
                                <p class="text-xs text-emerald-800 leading-relaxed">
                                    Pengajuan retur telah disetujui. Kurir <strong>{{ $pengembalian->return_courier }}</strong> akan datang menjemput barang ke alamat Anda atau Anda dapat menyerahkan paket ke outlet kurir terdekat dengan menyebutkan nomor resi ini.
                                </p>
                                <div class="p-3 bg-white border border-emerald-100 rounded-lg flex items-center justify-between text-xs shadow-sm">
                                    <div>
                                        <span class="text-gray-400 text-[10px] uppercase font-bold block">Nomor Resi AWB Retur</span>
                                        <span class="font-mono font-black text-emerald-700 text-sm tracking-widest">{{ $pengembalian->return_tracking_number }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-400 text-[10px] uppercase font-bold block">Kurir</span>
                                        <span class="font-bold text-gray-800">{{ $pengembalian->return_courier }}</span>
                                    </div>
                                </div>
                                @if($pengembalian->shipped_back_at)
                                    <p class="text-[10px] text-emerald-700 italic">
                                        <i class="fa-regular fa-clock mr-1"></i>Diproses pada {{ $pengembalian->shipped_back_at->translatedFormat('d F Y, H:i') }} WIB
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- ── Hasil akhir ── --}}
                        @if($pengembalian->status === 'completed')
                            <div class="retur-selesai-kotak">
                                @if($pengembalian->resolution === 'refund' && $pengembalian->refund_amount)
                                    <p class="retur-selesai-judul">
                                        <i class="fa-solid fa-circle-check"></i> Dana sudah dikembalikan
                                    </p>
                                    <p class="retur-selesai-nominal">Rp {{ number_format($pengembalian->refund_amount, 0, ',', '.') }}</p>
                                    <p class="retur-selesai-ket">Sudah masuk ke saldo R_Pay-mu.</p>
                                    <a href="{{ route('rpay.index') }}" class="retur-status-tautan">
                                        <i class="fa-solid fa-wallet"></i> Lihat saldo R_Pay
                                    </a>
                                @else
                                    <p class="retur-selesai-judul">
                                        <i class="fa-solid fa-circle-check"></i> Penukaran selesai diproses
                                    </p>
                                    <p class="retur-selesai-ket">
                                        Barang pengganti ({{ $pengembalian->exchange_request }}) sedang kami siapkan dan kirimkan.
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if($pengembalian->admin_notes && ! $pengembalian->ditolak)
                        <div class="retur-status-catatan">
                            <span class="font-bold">Catatan dari kami:</span> {{ $pengembalian->admin_notes }}
                        </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        {{-- DETAIL BARANG BELANJA & ALAMAT --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

            <div class="md:col-span-2 space-y-6">

                {{-- Item List Card --}}
                <div class="bg-white border border-border rounded-sm p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider border-b border-border pb-3">
                        Daftar Barang Belanja
                    </h3>

                    <div class="divide-y divide-border">
                        @foreach($order->items as $item)
                            <div class="py-4 flex gap-4 items-center">
                                <div class="h-16 w-16 bg-gray-50 border border-border rounded-sm flex-shrink-0 flex items-center justify-center p-1">
                                    <img src="{{ $item->product?->image_url ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80' }}"
                                         alt="{{ $item->product_name }}"
                                         class="object-contain max-h-full max-w-full"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80';">
                                </div>
                                <div class="min-w-0 flex-grow text-xs">
                                    <h4 class="font-bold text-text truncate text-sm">{{ $item->product_name }}</h4>
                                    @if($item->variant_info)
                                        <span class="inline-block bg-gray-100 text-text-light text-[10px] font-semibold px-2 py-0.5 rounded-sm mt-1">
                                            {{ $item->variant_info }}
                                        </span>
                                    @endif
                                    <p class="text-xs text-text-light mt-1">
                                        {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-xs font-bold text-text text-right flex-shrink-0">
                                    Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Shipping Address Card --}}
                <div class="bg-white border border-border rounded-sm p-6 shadow-sm space-y-3 text-xs">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider border-b border-border pb-3">
                        Informasi Pengiriman
                    </h3>

                    <div class="space-y-2 text-text">
                        <p class="font-bold text-text text-sm">
                            {{ $order->shipping_address['recipient_name'] ?? Auth::user()->name }} 
                            <span class="text-xs font-normal text-text-light">({{ $order->shipping_address['phone'] ?? '' }})</span>
                        </p>
                        <p class="leading-relaxed text-text-light">
                            {{ $order->shipping_address['address_line'] ?? '' }}<br>
                            {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['province'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}
                        </p>
                        <div class="pt-2 border-t border-dashed border-border flex items-center gap-2 text-text-light">
                            <i class="fa-solid fa-truck text-primary"></i>
                            <span>Kurir Pilihan: <strong class="text-primary font-bold">{{ $order->courier }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <div class="space-y-6">
                <div class="bg-white border border-border rounded-sm p-6 shadow-sm space-y-4 text-xs">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider border-b border-border pb-3">
                        Ringkasan Pembayaran
                    </h3>

                    <div class="space-y-2.5 text-text-light">
                        <div class="flex justify-between">
                            <span>Subtotal Barang</span>
                            <span class="text-text font-semibold">{{ $order->formatted_total_price }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Ongkos Kirim</span>
                            <span class="text-text font-semibold">{{ $order->formatted_shipping_cost }}</span>
                        </div>
                        <div class="border-t border-border pt-3 flex justify-between font-black text-sm text-primary uppercase">
                            <span>Grand Total</span>
                            <span class="text-accent text-base">{{ $order->formatted_grand_total }}</span>
                        </div>
                    </div>

                    <div class="border-t border-border pt-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-text-light">Metode Bayar:</span>
                            <span class="font-bold text-primary uppercase">{{ $order->payment_method }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-text-light">Status Bayar:</span>
                            <span class="px-2.5 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider
                                @if($order->payment_status === 'paid') bg-emerald-100 text-emerald-900 border border-emerald-300
                                @elseif($order->payment_status === 'pending_verification') bg-amber-100 text-amber-900 border border-amber-300
                                @else bg-red-100 text-red-900 border border-red-300
                                @endif">
                                {{ $order->payment_status_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Pay Now Button (If unpaid) --}}
                    @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                        <div class="pt-2">
                            <a href="{{ route('checkout.payment', $order->order_number) }}"
                               class="block w-full bg-accent hover:bg-accent-dark text-white font-bold py-3 rounded-sm text-center transition uppercase tracking-wider shadow-sm">
                                <i class="fa-solid fa-wallet mr-1"></i> Bayar Sekarang
                            </a>
                        </div>
                    @elseif($order->payment_status === 'pending_verification')
                        <div class="pt-2 bg-amber-50 border border-amber-200 rounded-sm p-3 text-center">
                            <p class="text-xs text-amber-900 font-semibold">
                                <i class="fa-solid fa-clock mr-1 text-amber-600"></i>
                                Pembayaran Anda sedang diverifikasi oleh tim admin kami. Pesanan akan diproses segera setelah dikonfirmasi.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Invoice hanya ada setelah pembayaran lunas --}}
                @if($order->invoice_number)
                    <a href="{{ route('orders.invoice', $order->order_number) }}"
                       class="block w-full mb-3 border border-primary text-primary hover:bg-primary hover:text-white text-center font-bold text-xs py-3 rounded-sm transition uppercase tracking-wider">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i>
                        Lihat Invoice
                    </a>
                @endif

                <a href="{{ route('products.index') }}"
                   class="block w-full bg-primary hover:bg-primary-light text-white text-center font-bold text-xs py-3 rounded-sm transition uppercase tracking-wider">
                    Belanja Lagi
                </a>
            </div>
        </div>

        {{-- ── Ajakan gabung program afiliasi ── --}}
        <a href="{{ route('affiliate') }}" class="promo-saldo">
            <span class="promo-saldo-kilau" aria-hidden="true"></span>

            <span class="promo-saldo-ikon" aria-hidden="true">
                <i class="fa-solid fa-coins"></i>
            </span>

            <span class="promo-saldo-teks">
                <span class="promo-saldo-atas">
                    <span class="promo-saldo-lencana">Promo</span>
                    <span class="promo-saldo-judul">Dapatkan Saldo Record Setiap Penjualan</span>
                </span>
                <span class="promo-saldo-ket">
                    Bagikan kode referalmu — setiap teman yang belanja memberimu
                    komisi langsung ke saldo R_Pay.
                </span>
            </span>

            <span class="promo-saldo-kaki">
                <span class="promo-saldo-tanya">Pelajari lebih lanjut?</span>
                <span class="promo-saldo-aksi">
                    Click Here
                    <i class="fa-solid fa-arrow-right"></i>
                </span>
            </span>
        </a>
    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Beri penilaian produk                             --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    @if($belumDinilai->isNotEmpty())
        @php
            $maksFotoUlasan = (int) config('ulasan.maks_foto', 3);
            $labelBintang   = config('ulasan.label_bintang', []);
            // Modal dibuka ulang bila validasi gagal, agar isian pembeli
            // tidak hilang begitu saja.
            $galatNilai = $errors->hasAny(['penilaian']) || collect($errors->keys())
                ->contains(fn ($k) => str_starts_with($k, 'penilaian.'));
        @endphp

        <div x-data="{
                {{-- Terbuka sendiri begitu pesanan selesai dan masih ada --}}
                buka: true,
                maksFoto: {{ $maksFotoUlasan }},
                maksFotoMb: {{ (int) config('ulasan.maks_foto_mb', 2) }},
                maksSisi: {{ (int) config('ulasan.maks_sisi_foto', 1600) }},
                sasaran: {{ config('ulasan.sasaran_pemampatan', 0.9) }},
                labelBintang: {{ Js::from($labelBintang) }},

                {{-- Keadaan per baris pesanan: bintang, bintang yang sedang
                     disorot tetikus, nama berkas foto, dan galatnya. --}}
                nilai: {{ Js::from($belumDinilai->mapWithKeys(fn ($i) => [$i->id => 0])) }},
                sorot: {},
                foto: {{ Js::from($belumDinilai->mapWithKeys(fn ($i) => [$i->id => []])) }},
                galatFoto: {},
                sedangProses: '',

                get semuaDinilai() {
                    return Object.values(this.nilai).every((n) => n > 0);
                },

                get jumlahBelum() {
                    return Object.values(this.nilai).filter((n) => n === 0).length;
                },

                beriBintang(id, n) { this.nilai[id] = n; },

                {{-- Bintang yang tampil: yang disorot tetikus lebih dulu, --}}
                bintangTampil(id) { return this.sorot[id] || this.nilai[id] || 0; },

                async pilihFoto(e, id) {
                    const masukan = e.target;
                    const berkas = Array.from(masukan.files);
                    this.galatFoto[id] = '';

                    if (! berkas.length) return;

                    if (berkas.length > this.maksFoto) {
                        this.galatFoto[id] = 'Maksimal ' + this.maksFoto + ' foto per produk.';
                        masukan.value = '';
                        this.foto[id] = [];
                        return;
                    }

                    if (! window.PemampatBerkas) {
                        this.galatFoto[id] = 'Komponen pengecil foto belum termuat. Muat ulang halaman (Ctrl+F5).';
                        masukan.value = '';
                        return;
                    }

                    this.sedangProses = String(id);
                    const batas = this.maksFotoMb * 1024 * 1024;
                    const hasil = [];

                    try {
                        for (const b of berkas) {
                            const h = await window.PemampatBerkas.foto(b, {
                                sasaranByte: Math.floor(batas * this.sasaran),
                                maksSisi: this.maksSisi,
                            });
                            hasil.push(h.berkas);
                        }

                        {{-- Seluruh berkas dipasang kembali sekaligus, sebab
                             satu masukan menampung banyak foto. --}}
                        const dt = new DataTransfer();
                        hasil.forEach((b) => dt.items.add(b));
                        masukan.files = dt.files;

                        {{-- Alamat sementara dibuat supaya fotonya benar-benar --}}
                        this.bebaskanAlamat(id);

                        this.foto[id] = hasil.map((b) => ({
                            nama: b.name,
                            ukuran: this.ukuranBerkas(b.size),
                            alamat: URL.createObjectURL(b),
                        }));
                    } catch (galat) {
                        this.galatFoto[id] = galat.message;
                        masukan.value = '';
                        this.bebaskanAlamat(id);
                        this.foto[id] = [];
                    } finally {
                        this.sedangProses = '';
                    }
                },

                // Membuang satu foto dari pilihan.
                buangFoto(id, urutan) {
                    {{-- Pemilihnya memakai kutip TUNGGAL di dalam kurung siku. --}}
                    const masukan = document.querySelector(
                        'input[name=\'penilaian[' + id + '][photos][]\']');

                    const sisa = Array.from(masukan.files).filter((_, i) => i !== urutan);

                    const dt = new DataTransfer();
                    sisa.forEach((b) => dt.items.add(b));
                    masukan.files = dt.files;

                    URL.revokeObjectURL(this.foto[id][urutan].alamat);
                    this.foto[id] = this.foto[id].filter((_, i) => i !== urutan);
                },

                // Foto hasil pemampatan kerap jauh di bawah 1 MB.
                ukuranBerkas(byte) {
                    return byte < 1048576
                        ? Math.max(1, Math.round(byte / 1024)) + ' KB'
                        : (byte / 1048576).toFixed(1) + ' MB';
                },

                // Alamat sementara dilepas begitu tidak dipakai; kalau tidak, berkasnya tetap dipegang peramban sam...
                bebaskanAlamat(id) {
                    (this.foto[id] || []).forEach((f) => URL.revokeObjectURL(f.alamat));
                },

                tutup() { this.buka = false; },

                init() {
                    this.$watch('buka', (n) => { document.body.style.overflow = n ? 'hidden' : ''; });
                    document.body.style.overflow = 'hidden';
                },
                destroy() {
                    document.body.style.overflow = '';
                    Object.keys(this.foto).forEach((id) => this.bebaskanAlamat(id));
                }
             }"
             @buka-penilaian.window="buka = true"
             @keydown.escape.window="tutup()"
             x-show="buka"
             x-cloak
             class="batal-lapis">

            <div class="batal-kotak nilai-kotak" @click.outside="tutup()">

                <div class="batal-kepala">
                    <div>
                        <h3 class="batal-judul">Bagaimana Produknya?</h3>
                        <p class="batal-nomor">{{ $order->order_number }}</p>
                    </div>
                    <button type="button" @click="tutup()" class="batal-tutup" aria-label="Tutup">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                @if($galatNilai)
                    <div class="galat-ringkas">
                        <p class="galat-ringkas-judul">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Penilaianmu belum terkirim
                        </p>
                        <ul>
                            @foreach($errors->all() as $pesan)
                                <li class="galat-ringkas-isi">{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('orders.review', $order->order_number) }}" method="POST"
                      enctype="multipart/form-data" class="batal-form">
                    @csrf

                    <div class="batal-isi">
                        <p class="nilai-pengantar">
                            Penilaianmu tayang di halaman produk dan membantu pembeli lain
                            memutuskan. Bintangnya wajib; komentar dan foto boleh dikosongkan.
                        </p>

                        @foreach($belumDinilai as $item)
                            <div class="nilai-produk">
                                <div class="nilai-produk-kepala">
                                    @if($item->product?->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                             alt="{{ $item->product_name }}" class="nilai-gambar">
                                    @else
                                        <span class="nilai-gambar nilai-gambar-kosong">
                                            <i class="fa-solid fa-shoe-prints"></i>
                                        </span>
                                    @endif

                                    <div class="nilai-produk-teks">
                                        <p class="nilai-produk-nama">{{ $item->product_name }}</p>
                                        @if($item->variant_info)
                                            <p class="nilai-produk-varian">{{ $item->variant_info }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Bintang. --}}
                                <div class="nilai-bintang-baris">
                                    <div class="nilai-bintang" @mouseleave="sorot[{{ $item->id }}] = 0">
                                        @for($b = 1; $b <= 5; $b++)
                                            <label class="nilai-bintang-satu"
                                                   @mouseenter="sorot[{{ $item->id }}] = {{ $b }}">
                                                <input type="radio"
                                                       name="penilaian[{{ $item->id }}][rating]"
                                                       value="{{ $b }}"
                                                       @change="beriBintang({{ $item->id }}, {{ $b }})"
                                                       class="nilai-radio">
                                                <i class="fa-solid fa-star"
                                                   :class="bintangTampil({{ $item->id }}) >= {{ $b }}
                                                       ? 'nilai-bintang-aktif' : 'nilai-bintang-mati'"></i>
                                                <span class="sr-only">{{ $b }} bintang</span>
                                            </label>
                                        @endfor
                                    </div>

                                    <span class="nilai-bintang-label"
                                          x-show="bintangTampil({{ $item->id }}) > 0" x-cloak
                                          x-text="labelBintang[bintangTampil({{ $item->id }})]"></span>
                                </div>

                                <textarea name="penilaian[{{ $item->id }}][comment]" rows="2"
                                          class="retur-input nilai-komentar"
                                          placeholder="Ceritakan pengalamanmu — bahannya, ukurannya, kenyamanannya. Boleh dikosongkan."></textarea>

                                <label class="nilai-foto">
                                    <input type="file"
                                           name="penilaian[{{ $item->id }}][photos][]"
                                           accept="image/jpeg,image/png,image/webp"
                                           multiple
                                           @change="pilihFoto($event, {{ $item->id }})"
                                           class="bukti-masukan">

                                    <i class="fa-solid fa-camera"
                                       x-show="sedangProses !== '{{ $item->id }}'"></i>
                                    <i class="fa-solid fa-spinner bukti-putar"
                                       x-show="sedangProses === '{{ $item->id }}'" x-cloak></i>

                                    <span x-show="sedangProses === '{{ $item->id }}'" x-cloak>
                                        Mengecilkan foto…
                                    </span>
                                    <span x-show="sedangProses !== '{{ $item->id }}' && !foto[{{ $item->id }}].length">
                                        Tambah foto (opsional, maks {{ $maksFotoUlasan }})
                                    </span>
                                    <span x-show="sedangProses !== '{{ $item->id }}' && foto[{{ $item->id }}].length"
                                          x-cloak
                                          x-text="'Ganti foto (' + foto[{{ $item->id }}].length + ' terpasang)'"></span>
                                </label>

                                {{-- Pratinjau foto yang terpasang. --}}
                                <div class="nilai-foto-daftar" x-show="foto[{{ $item->id }}].length" x-cloak>
                                    <template x-for="(f, i) in foto[{{ $item->id }}]" :key="f.alamat">
                                        <div class="nilai-foto-item">
                                            <img :src="f.alamat" :alt="'Foto ulasan: ' + f.nama">

                                            {{-- Tombol buang berada di dalam kotak --}}
                                            <button type="button"
                                                    @click="buangFoto({{ $item->id }}, i)"
                                                    class="nilai-foto-buang"
                                                    :aria-label="'Buang foto ' + f.nama">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>

                                            <span class="nilai-foto-ukuran" x-text="f.ukuran"></span>
                                        </div>
                                    </template>
                                </div>

                                <p class="galat-retur" x-show="galatFoto[{{ $item->id }}]" x-cloak
                                   x-text="galatFoto[{{ $item->id }}]"></p>
                                @error('penilaian.' . $item->id . '.photos.*')
                                    <p class="galat-retur">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="retur-kaki">
                        <p class="retur-kaki-sisa" x-show="!semuaDinilai" x-cloak>
                            <i class="fa-solid fa-circle-info"></i>
                            <span x-text="jumlahBelum === 1
                                ? 'Masih ada 1 produk yang belum diberi bintang'
                                : 'Masih ada ' + jumlahBelum + ' produk yang belum diberi bintang'"></span>
                        </p>

                        <p class="retur-kaki-siap" x-show="semuaDinilai" x-cloak>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Siap dikirim — terima kasih sudah menilai</span>
                        </p>

                        <div class="retur-kaki-tombol">
                            <button type="button" @click="tutup()" class="retur-tombol retur-tombol-nanti">
                                Nanti Saja
                            </button>

                            <button type="submit" class="retur-tombol retur-tombol-kirim"
                                    :disabled="!semuaDinilai || sedangProses !== ''">
                                <i class="fa-solid fa-star"></i>
                                <span>Kirim Penilaian</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Pilih alasan pembatalan                           --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    @php $jalurModal = app(\App\Services\PembatalanPesananService::class)->jalur($order); @endphp
    @if(in_array($jalurModal, [
        \App\Services\PembatalanPesananService::LANGSUNG_TANPA_DANA,
        \App\Services\PembatalanPesananService::LANGSUNG_REFUND,
    ], true))
        @php
            $pilihanAlasan = config('alasan-batal.pilihan', []);
            $wajibJelas    = config('alasan-batal.wajib_penjelasan');
        @endphp

        <div x-data="{
                {{-- Modal dibuka ulang otomatis bila validasi gagal, agar isian
                     pembeli tidak hilang begitu saja --}}
                buka: {{ $errors->any() ? 'true' : 'false' }},
                alasan: '{{ old('alasan') }}',
                penjelasan: '{{ old('penjelasan') }}',
                get perluPenjelasan() { return this.alasan === '{{ $wajibJelas }}'; },
                get bisaKirim() {
                    if (this.alasan === '') return false;
                    return ! this.perluPenjelasan || this.penjelasan.trim().length > 0;
                },
                tutup() { this.buka = false; },

                {{-- Halaman di belakang dikunci agar tidak ikut bergulir
                     saat modal terbuka, lalu dilepas kembali saat ditutup --}}
                init() {
                    {{-- Modal bisa langsung terbuka saat validasi gagal,
                         jadi kondisi awal ikut diterapkan --}}
                    if (this.buka) document.body.style.overflow = 'hidden';

                    this.$watch('buka', nilai => {
                        document.body.style.overflow = nilai ? 'hidden' : '';
                    });
                },
                destroy() { document.body.style.overflow = ''; }
             }"
             @buka-batal.window="buka = true"
             @keydown.escape.window="tutup()"
             x-show="buka"
             x-cloak
             class="batal-lapis">

            <div class="batal-kotak" @click.outside="tutup()">

                {{-- Kepala --}}
                <div class="batal-kepala">
                    <div>
                        <h3 class="batal-judul">Batalkan Pesanan</h3>
                        <p class="batal-nomor">{{ $order->order_number }}</p>
                    </div>
                    <button type="button" @click="tutup()" class="batal-tutup" aria-label="Tutup">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST"
                      class="batal-form">
                    @csrf

                    <div class="batal-isi">
                        <p class="batal-tanya">Boleh tahu alasannya?</p>
                        <p class="batal-catatan">
                            Jawabanmu membantu kami memperbaiki layanan. Stok produk akan
                            dikembalikan setelah pesanan dibatalkan.
                        </p>

                        @error('alasan')
                            <p class="batal-galat">{{ $message }}</p>
                        @enderror

                        {{-- Daftar pilihan alasan --}}
                        <div class="batal-pilihan">
                            @foreach($pilihanAlasan as $kunci => $label)
                                <label class="batal-opsi" :class="alasan === '{{ $kunci }}' && 'batal-opsi-aktif'">
                                    <input type="radio" name="alasan" value="{{ $kunci }}"
                                           x-model="alasan" class="batal-radio">
                                    <span class="batal-opsi-teks">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Kolom penjelasan: wajib bila memilih "Alasan lain",
                             opsional untuk pilihan lainnya --}}
                        <div class="batal-jelas" x-show="alasan !== ''" x-cloak>
                            <label class="batal-label">
                                <span x-text="perluPenjelasan ? 'Jelaskan alasanmu' : 'Catatan tambahan'"></span>
                                <span class="batal-wajib" x-show="perluPenjelasan">wajib diisi</span>
                                <span class="batal-opsional" x-show="!perluPenjelasan">opsional</span>
                            </label>

                            <textarea name="penjelasan" rows="3" maxlength="500"
                                      x-model="penjelasan"
                                      class="batal-textarea"
                                      :placeholder="perluPenjelasan
                                        ? 'Ceritakan sedikit alasan pembatalanmu...'
                                        : 'Ada yang ingin kamu sampaikan? (boleh dikosongkan)'"></textarea>

                            <div class="batal-hitung">
                                <span x-text="penjelasan.length + ' / 500'"></span>
                            </div>

                            @error('penjelasan')
                                <p class="batal-galat">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Kaki --}}
                    <div class="batal-kaki">
                        <button type="button" @click="tutup()" class="batal-tombol batal-tombol-batal">
                            Jangan Batalkan
                        </button>
                        <button type="submit" :disabled="!bisaKirim"
                                class="batal-tombol batal-tombol-kirim"
                                :class="!bisaKirim && 'batal-tombol-mati'">
                            <i class="fa-solid fa-xmark"></i>
                            Ya, Batalkan Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>


    @endif

    {{-- Gaya kerangka modal dipakai bersama oleh modal pembatalan dan --}}
    @push('styles')
        <style>
            /* ══════════ Modal penilaian produk ══════════ Memakai kembali kerangka .batal-* yang sudah terbukt... */

            .nilai-kotak { max-width: 560px; }

            /* ── Ajakan menilai di halaman pesanan ── */
            .nilai-ajakan {
                display: flex; align-items: center; justify-content: space-between;
                gap: 14px; flex-wrap: wrap;
                padding: 14px 16px;
                border: 1px solid #FCD34D; border-radius: 10px;
                background: linear-gradient(135deg, #FFFBEB, #FEF3C7);
            }

            .nilai-ajakan-teks { min-width: 0; flex: 1 1 240px; }

            .nilai-ajakan-judul {
                display: flex; align-items: center; gap: 7px;
                font-size: 13px; font-weight: 800; color: #92400E;
            }
            .nilai-ajakan-judul i { color: #F59E0B; }

            .nilai-ajakan-ket {
                margin-top: 3px;
                font-size: 11.5px; line-height: 1.55; color: #A16207;
            }

            .nilai-ajakan-tombol {
                flex: none;
                padding: 9px 18px;
                border: 0; border-radius: 8px;
                background: #F59E0B; color: #fff;
                font-size: 11.5px; font-weight: 800;
                letter-spacing: .05em; text-transform: uppercase;
                cursor: pointer;
                transition: background-color 160ms ease;
            }
            .nilai-ajakan-tombol:hover { background: #D97706; }

            .nilai-pengantar {
                margin-bottom: 14px;
                font-size: 12.5px; line-height: 1.6; color: #4b5563;
            }

            .nilai-produk {
                padding: 14px;
                border: 1px solid #e5e7eb; border-radius: 12px;
                background: #fff;
            }
            .nilai-produk + .nilai-produk { margin-top: 12px; }

            .nilai-produk-kepala { display: flex; gap: 11px; align-items: center; }

            .nilai-gambar {
                flex: none;
                width: 46px; height: 46px;
                border-radius: 9px; object-fit: cover;
                background: #f3f4f6;
            }
            .nilai-gambar-kosong {
                display: inline-flex; align-items: center; justify-content: center;
                color: #9ca3af;
            }

            .nilai-produk-teks { min-width: 0; }

            /* Nama produk di toko ini panjang-panjang; dibatasi dua baris agar tidak mendorong bintangnya jauh ... */
            .nilai-produk-nama {
                font-size: 12.5px; font-weight: 700; color: #111827; line-height: 1.4;
                display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .nilai-produk-varian {
                margin-top: 2px;
                font-size: 11px; color: #6b7280;
            }

            .nilai-bintang-baris {
                display: flex; align-items: center; gap: 10px;
                flex-wrap: wrap;
                margin-top: 12px;
            }

            .nilai-bintang { display: inline-flex; gap: 3px; }

            .nilai-bintang-satu {
                position: relative;
                cursor: pointer;
                padding: 3px;
                font-size: 23px; line-height: 1;
            }

            /* Radio aslinya disembunyikan tetapi tetap ada: itu yang membuat bintangnya terkirim bersama borang... */
            .nilai-radio {
                position: absolute; opacity: 0;
                width: 100%; height: 100%; left: 0; top: 0;
                margin: 0; cursor: pointer;
            }
            .nilai-radio:focus-visible + i {
                outline: 2px solid #2563eb; outline-offset: 3px; border-radius: 3px;
            }

            .nilai-bintang-aktif { color: #F59E0B; }
            .nilai-bintang-mati  { color: #d8dce3; }

            .nilai-bintang-satu i { transition: transform 140ms ease, color 140ms ease; }
            .nilai-bintang-satu:hover i { transform: scale(1.16); }

            .nilai-bintang-label {
                font-size: 11.5px; font-weight: 700; color: #B45309;
            }

            .nilai-komentar { margin-top: 11px; }

            .nilai-foto {
                display: inline-flex; align-items: center; gap: 8px;
                margin-top: 10px;
                padding: 8px 13px;
                border: 1.5px dashed #d1d5db; border-radius: 9px;
                background: #fafafa; cursor: pointer;
                font-size: 11.5px; font-weight: 600; color: #4b5563;
                transition: border-color 160ms ease, background-color 160ms ease;
            }
            .nilai-foto:hover { border-color: #9ca3af; background: #f5f5f5; }

            /* ── Pratinjau foto yang terpasang ── */
            .nilai-foto-daftar {
                display: flex; gap: 8px; flex-wrap: wrap;
                margin-top: 10px;
            }

            .nilai-foto-item {
                position: relative;
                width: 76px; height: 76px;
                border: 1px solid #e5e7eb; border-radius: 9px;
                overflow: hidden; background: #f3f4f6;
            }
            .nilai-foto-item img { width: 100%; height: 100%; object-fit: cover; display: block; }

            .nilai-foto-buang {
                position: absolute; top: 3px; right: 3px;
                display: inline-flex; align-items: center; justify-content: center;
                width: 20px; height: 20px;
                border: 0; border-radius: 50%;
                background: rgb(17 24 39 / .72); color: #fff;
                font-size: 10px; line-height: 1; cursor: pointer;
                transition: background-color 160ms ease;
            }
            .nilai-foto-buang:hover { background: #DC2626; }

            /* Ukuran hasil pemampatan disebutkan, supaya pembeli tahu fotonya memang sudah dikecilkan dan tidak... */
            .nilai-foto-ukuran {
                position: absolute; left: 0; right: 0; bottom: 0;
                padding: 2px 4px;
                background: rgb(17 24 39 / .62); color: #fff;
                font-size: 9px; font-weight: 700; text-align: center;
            }

            /* Hanya untuk pembaca layar: teks yang menjelaskan tiap bintang. */
            .sr-only {
                position: absolute; width: 1px; height: 1px;
                padding: 0; margin: -1px; overflow: hidden;
                clip: rect(0,0,0,0); white-space: nowrap; border: 0;
            }

            /* ══════════ Ajakan program afiliasi ══════════ Ditulis tangan seperti blok lain di halaman ini: ha... */

            .promo-saldo {
                position: relative;
                display: flex;
                align-items: center;
                gap: 18px;
                padding: 18px 22px;
                border-radius: 10px;
                overflow: hidden;

                /* Biru tua ke ungu-tua: cukup berbeda dari kartu putih di sekitarnya agar tertangkap mata, tanpa be... */
                background:
                    radial-gradient(90% 200% at 100% 0%, #2C5AA0 0%, transparent 60%),
                    linear-gradient(115deg, #16294B 0%, #1E3A6B 60%, #24305E 100%);

                color: #fff;
                text-decoration: none;
                box-shadow: 0 2px 10px rgb(16 32 62 / .18);
                transition: transform 260ms cubic-bezier(.2,.8,.3,1),
                            box-shadow 260ms ease;
            }

            .promo-saldo:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 22px rgb(16 32 62 / .30);
            }

            /* Tepi tipis di dalam, supaya kartunya punya batas yang jelas tanpa garis tegas yang membuatnya ter... */
            .promo-saldo::after {
                content: '';
                position: absolute; inset: 0;
                border-radius: 10px;
                border: 1px solid rgb(255 255 255 / .12);
                pointer-events: none;
            }

            /* Kilau menyapu pelan. */
            .promo-saldo-kilau {
                position: absolute; inset: 0;
                background: linear-gradient(100deg,
                    transparent 40%,
                    rgb(255 255 255 / .13) 50%,
                    transparent 60%);
                background-size: 250% 100%;
                background-repeat: no-repeat;
                animation: promo-sapu 7s ease-in-out infinite;
                pointer-events: none;
            }

            @keyframes promo-sapu {
                0%, 8%    { background-position: 170% 0; }
                45%, 100% { background-position: -70% 0; }
            }

            .promo-saldo-ikon {
                flex: none;
                display: inline-flex; align-items: center; justify-content: center;
                width: 46px; height: 46px;
                border-radius: 12px;
                background: linear-gradient(140deg, #FBBF24, #F59E0B);
                color: #4A2E05;
                font-size: 19px;
                box-shadow: 0 3px 9px rgb(245 158 11 / .35);
            }

            /* Bagian teks yang memuai mengisi ruang di tengah — inilah yang membuat lencana tetap di kiri dan a... */
            .promo-saldo-teks { flex: 1 1 auto; min-width: 0; }

            .promo-saldo-atas {
                display: flex; align-items: center; gap: 10px;
                flex-wrap: wrap;
            }

            .promo-saldo-lencana {
                flex: none;
                padding: 3px 9px;
                border-radius: 999px;
                background: #E11D48;
                font-size: 9px; font-weight: 800;
                letter-spacing: .12em; text-transform: uppercase;
            }

            .promo-saldo-judul {
                font-size: 15px; font-weight: 800; line-height: 1.3;
                letter-spacing: -.01em;
            }

            .promo-saldo-ket {
                display: block;
                margin-top: 5px;
                font-size: 12px; line-height: 1.55;
                color: rgb(255 255 255 / .72);
                max-width: 62ch;
            }

            /* Pemisah tegak, bukan garis mendatar: di tata letak melintang itulah yang memisahkan bacaan dari a... */
            .promo-saldo-kaki {
                flex: none;
                display: flex; align-items: center; gap: 14px;
                padding-left: 20px;
                border-left: 1px solid rgb(255 255 255 / .14);
            }

            .promo-saldo-tanya {
                font-size: 11.5px;
                color: rgb(255 255 255 / .68);
            }

            .promo-saldo-aksi {
                display: inline-flex; align-items: center; gap: 7px;
                white-space: nowrap;
                font-size: 11.5px; font-weight: 800;
                letter-spacing: .04em; text-transform: uppercase;
                color: #FCD34D;
            }

            /* Panah bergeser sedikit saat disentuh — isyarat arah, bukan hiasan yang bergerak sendiri terus-men... */
            .promo-saldo-aksi i {
                font-size: 10px;
                transition: transform 260ms cubic-bezier(.2,.8,.3,1);
            }
            .promo-saldo:hover .promo-saldo-aksi i { transform: translateX(4px); }

            /* Di layar sempit, melintang tidak lagi muat: isinya ditumpuk dan pemisah tegaknya berubah jadi gar... */
            @media (max-width: 720px) {
                .promo-saldo { flex-wrap: wrap; gap: 14px; padding: 16px 18px; }

                .promo-saldo-ikon { width: 40px; height: 40px; font-size: 17px; }

                .promo-saldo-teks { flex: 1 1 220px; }

                .promo-saldo-kaki {
                    flex: 1 1 100%;
                    justify-content: space-between;
                    padding-left: 0; padding-top: 12px;
                    border-left: 0;
                    border-top: 1px solid rgb(255 255 255 / .14);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .promo-saldo-kilau { display: none; }
                .promo-saldo, .promo-saldo-aksi i { transition: none; }
                .promo-saldo:hover { transform: none; }
            }

            /* Modal alasan pembatalan — CSS sendiri agar tidak bergantung pada hasil build Tailwind. */
            [x-cloak] { display: none !important; }

            .batal-lapis {
                position: fixed;
                inset: 0;
                z-index: 60;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: rgb(15 23 42 / 0.55);
            }

            .batal-kotak {
                width: 100%;
                max-width: 480px;
                max-height: 90vh;
                /* dvh mengikuti tinggi layar sebenarnya di HP, saat bilah alamat browser muncul-hilang. */
                max-height: 90dvh;
                display: flex;
                flex-direction: column;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.35);
                overflow: hidden;
            }

            .batal-kepala {
                flex-shrink: 0;   /* kepala tetap terlihat saat isi digulir */
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                padding: 18px 20px;
                border-bottom: 1px solid #e5e7eb;
            }
            .batal-judul {
                font-size: 15px;
                font-weight: 800;
                color: #1f2937;
            }
            .batal-nomor {
                font-size: 12px;
                color: #9ca3af;
                margin-top: 2px;
            }
            .batal-tutup {
                flex-shrink: 0;
                width: 30px;
                height: 30px;
                border: 0;
                background: none;
                color: #9ca3af;
                border-radius: 6px;
                cursor: pointer;
            }
            .batal-tutup:hover { background: #f3f4f6; color: #374151; }

            /* Form ikut jadi kolom flex, sebab isi & kaki modal berada di dalamnya — bukan langsung di bawah .b... */
            .batal-form {
                flex: 1 1 auto;
                min-height: 0;
                display: flex;
                flex-direction: column;
            }

            .batal-isi {
                /* min-height: 0 wajib ada. */
                flex: 1 1 auto;
                min-height: 0;
                padding: 20px;
                overflow-y: auto;
                /* Gulir berhenti di dalam modal, tidak merembet ke halaman */
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }

            /* Batang gulir yang lebih tipis dan tidak mencolok */
            .batal-isi::-webkit-scrollbar { width: 8px; }
            .batal-isi::-webkit-scrollbar-track { background: transparent; }
            .batal-isi::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 9999px;
            }
            .batal-isi::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
            .batal-isi { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }
            .batal-tanya {
                font-size: 14px;
                font-weight: 700;
                color: #374151;
            }
            .batal-catatan {
                font-size: 12px;
                line-height: 1.7;
                color: #6b7280;
                margin-top: 4px;
                margin-bottom: 16px;
            }

            .batal-galat {
                font-size: 12px;
                font-weight: 600;
                color: #b91c1c;
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 6px;
                padding: 8px 12px;
                margin-bottom: 12px;
            }

            /* Daftar pilihan */
            .batal-pilihan {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .batal-opsi {
                display: flex;
                align-items: center;
                gap: 11px;
                padding: 12px 14px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                cursor: pointer;
                transition: border-color 160ms ease, background-color 160ms ease;
            }
            .batal-opsi:hover { border-color: #cbd5e1; background: #f9fafb; }
            .batal-opsi-aktif {
                border-color: var(--color-primary, #1B3A6B);
                background: #eff6ff;
            }
            .batal-radio {
                flex-shrink: 0;
                width: 16px;
                height: 16px;
                accent-color: var(--color-primary, #1B3A6B);
                cursor: pointer;
            }
            .batal-opsi-teks {
                font-size: 13.5px;
                color: #374151;
                line-height: 1.5;
            }
            .batal-opsi-aktif .batal-opsi-teks { font-weight: 600; color: #1f2937; }

            /* Kolom penjelasan */
            .batal-jelas { margin-top: 18px; }
            .batal-label {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                font-weight: 700;
                color: #374151;
                margin-bottom: 7px;
            }
            .batal-wajib {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #b91c1c;
                background: #fef2f2;
                padding: 2px 7px;
                border-radius: 9999px;
            }
            .batal-opsional {
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #9ca3af;
            }
            .batal-textarea {
                width: 100%;
                padding: 11px 13px;
                font-size: 13px;
                line-height: 1.6;
                color: #1f2937;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                resize: vertical;
                outline: none;
                transition: border-color 180ms ease, box-shadow 180ms ease;
            }
            .batal-textarea:focus {
                border-color: var(--color-primary, #1B3A6B);
                box-shadow: 0 0 0 3px rgb(27 58 107 / 0.10);
            }
            .batal-hitung {
                text-align: right;
                font-size: 11px;
                color: #9ca3af;
                margin-top: 4px;
            }

            /* Kaki modal */
            .batal-kaki {
                flex-shrink: 0;   /* tombol tetap terlihat saat isi digulir */
                display: flex;
                flex-direction: column-reverse;
                gap: 8px;
                padding: 16px 20px;
                border-top: 1px solid #e5e7eb;
                background: #f9fafb;
            }
            @media (min-width: 480px) {
                .batal-kaki { flex-direction: row; justify-content: flex-end; }
            }
            .batal-tombol {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 11px 20px;
                font-size: 12.5px;
                font-weight: 700;
                border-radius: 6px;
                border: 1px solid transparent;
                cursor: pointer;
                transition: background-color 180ms ease, border-color 180ms ease;
            }
            .batal-tombol-batal {
                background: #fff;
                color: #4b5563;
                border-color: #d1d5db;
            }
            .batal-tombol-batal:hover { background: #f3f4f6; }
            .batal-tombol-kirim {
                background: #dc2626;
                color: #fff;
            }
            .batal-tombol-kirim:hover { background: #b91c1c; }
            .batal-tombol-mati {
                background: #e5e7eb;
                color: #9ca3af;
                cursor: not-allowed;
            }
            .batal-tombol-mati:hover { background: #e5e7eb; }
        </style>
    @endpush

        {{-- ══════════ MODAL PENGAJUAN PENGEMBALIAN ══════════ --}}
        @php
            $pilihanRetur = config('alasan-retur.pilihan', []);
            $ukuranDipesan = $order->items->pluck('variant_info')->filter()->values();
        @endphp

        @if(\App\Http\Controllers\ReturnController::bolehMengajukan($order))
        @php
            // Kalau pengajuannya ditolak validasi, modal dibuka kembali dengan
            // isian yang tadi — bukan dibiarkan tertutup sehingga pembeli harus
            // mengetik ulang semuanya tanpa tahu apa yang salah.
            $galatRetur = $errors->hasAny([
                'reason_code', 'reason', 'resolution', 'exchange_request',
                'receipt_photo', 'package_photo', 'unboxing_video', 'video_duration',
            ]);
        @endphp

        <div x-data="{
                buka: {{ $galatRetur ? 'true' : 'false' }},
                alasan: '{{ old('reason_code', '') }}',
                penyelesaian: '{{ old('resolution', '') }}',
                {{-- Js::from, bukan @json. --}}
                penjelasan: {{ Js::from(old('reason', '')) }},
                pilihan: {{ Js::from(collect($pilihanRetur)->keyBy('kode')) }},

                get penyelesaianTersedia() {
                    return this.alasan ? (this.pilihan[this.alasan]?.penyelesaian ?? []) : [];
                },

                // Penjelasan bebas hanya wajib untuk 'Alasan lain'.
                kodeWajib: {{ Js::from(config('alasan-retur.wajib_penjelasan', 'lainnya')) }},
                minimalPenjelasan: {{ (int) config('alasan-retur.minimal_penjelasan', 15) }},

                get penjelasanWajib() {
                    return this.alasan === this.kodeWajib;
                },

                get penjelasanTerisi() {
                    return this.penjelasan.trim().length >= this.minimalPenjelasan;
                },

                // ── Bukti wajib ── Nama berkas disimpan supaya pembeli bisa melihat mana yang sudah terpasang tanp...
                maksFotoMb:  {{ (int) config('alasan-retur.bukti.maks_foto_mb', 5) }},
                maksVideoMb: {{ (int) config('alasan-retur.bukti.maks_video_mb', 100) }},
                maksDetik:   {{ (int) config('alasan-retur.bukti.maks_durasi_detik', 120) }},

                sasaranPemampatan: {{ config('alasan-retur.bukti.sasaran_pemampatan', 0.9) }},
                maksSisiFoto:      {{ (int) config('alasan-retur.bukti.maks_sisi_foto', 2200) }},

                bukti:      { receipt_photo: '', package_photo: '', unboxing_video: '' },
                galatBukti: { receipt_photo: '', package_photo: '', unboxing_video: '' },
                // Keterangan hasil pemampatan, mis. '8,4 MB → 1,7 MB'.
                susutBukti: { receipt_photo: '', package_photo: '', unboxing_video: '' },
                // Nama kunci yang sedang diproses, dan kemajuannya dalam persen.
                sedangProses: '',
                kemajuan: 0,
                durasiVideo: null,

                get buktiLengkap() {
                    return ['receipt_photo', 'package_photo', 'unboxing_video']
                        .every((k) => this.bukti[k] && ! this.galatBukti[k]);
                },

                get sedangMemampatkan() { return this.sedangProses !== ''; },

                // Pemampat harus benar-benar ada sebelum berkas diterima.
                pastikanPemampat() {
                    if (! window.PemampatBerkas) {
                        throw new Error('Komponen pengecil berkas belum termuat. '
                            + 'Muat ulang halaman ini (Ctrl+F5), lalu coba lagi.');
                    }
                },

                // Foto di atas batas tidak ditolak, melainkan dikecilkan dulu.
                async pilihFoto(e, kunci) {
                    const masukan = e.target;
                    const berkas  = masukan.files[0];

                    this.bukti[kunci] = '';
                    this.galatBukti[kunci] = '';
                    this.susutBukti[kunci] = '';
                    if (! berkas) return;

                    const batas   = this.maksFotoMb * 1024 * 1024;
                    const sasaran = Math.floor(batas * this.sasaranPemampatan);

                    this.sedangProses = kunci;
                    this.kemajuan = 0;

                    try {
                        this.pastikanPemampat();

                        const hasil = await window.PemampatBerkas.foto(berkas, {
                            sasaranByte: sasaran,
                            maksSisi:    this.maksSisiFoto,
                        });

                        if (hasil.dimampatkan) {
                            window.PemampatBerkas.pasang(masukan, hasil.berkas);
                            this.susutBukti[kunci] = this.mb(hasil.asal) + ' MB → '
                                + this.mb(hasil.berkas.size) + ' MB';
                        }

                        this.bukti[kunci] = hasil.berkas.name;
                    } catch (galat) {
                        this.galatBukti[kunci] = galat.message;
                        masukan.value = '';
                    } finally {
                        this.sedangProses = '';
                    }
                },

                // Video: durasi diperiksa dulu, baru ukurannya dikecilkan.
                async pilihVideo(e) {
                    const masukan = e.target;
                    const berkas  = masukan.files[0];

                    this.bukti.unboxing_video = '';
                    this.galatBukti.unboxing_video = '';
                    this.susutBukti.unboxing_video = '';
                    this.durasiVideo = null;
                    if (! berkas) return;

                    this.sedangProses = 'unboxing_video';
                    this.kemajuan = 0;

                    try {
                        this.pastikanPemampat();

                        const detik = await window.PemampatBerkas.durasi(berkas);

                        if (detik !== null && detik > this.maksDetik) {
                            throw new Error('Durasinya ' + this.jam(detik)
                                + ', melebihi batas ' + this.jam(this.maksDetik) + '.');
                        }

                        const batas   = this.maksVideoMb * 1024 * 1024;
                        const sasaran = Math.floor(batas * this.sasaranPemampatan);

                        const hasil = await window.PemampatBerkas.video(berkas, {
                            sasaranByte: sasaran,
                            batasByte:   batas,
                            // Durasi diteruskan supaya tidak dibaca dua kali —
                            // pada berkas besar pembacaannya sendiri tidak murah.
                            durasi:      detik,
                            durasiAnggapan: this.maksDetik,
                            lapor:       (persen) => { this.kemajuan = persen; },
                        });

                        if (hasil.dimampatkan) {
                            window.PemampatBerkas.pasang(masukan, hasil.berkas);
                            this.susutBukti.unboxing_video = this.mb(hasil.asal) + ' MB → '
                                + this.mb(hasil.berkas.size) + ' MB';
                        }

                        this.durasiVideo = detik;
                        this.bukti.unboxing_video = hasil.berkas.name;
                    } catch (galat) {
                        this.galatBukti.unboxing_video = galat.message;
                        masukan.value = '';
                    } finally {
                        this.sedangProses = '';
                        this.kemajuan = 0;
                    }
                },

                mb(byte)   { return (byte / 1024 / 1024).toFixed(1); },
                jam(detik) {
                    const m = Math.floor(detik / 60);
                    const d = detik % 60;
                    return m + ' menit ' + String(d).padStart(2, '0') + ' detik';
                },

                // Tombol kirim hidup bila alasan & penyelesaian sudah dipilih,
                // penjelasannya cukup (khusus 'Alasan lain'), dan ketiga
                // buktinya sudah terpasang.
                get siapKirim() {
                    // Selama masih ada berkas yang diproses, borangnya dikunci:
                    // mengirim di tengah pemampatan berarti mengirim berkas asli
                    // yang belum diganti — tepat yang hendak dihindari.
                    if (this.sedangMemampatkan) return false;
                    if (! this.alasan || ! this.penyelesaian) return false;
                    if (this.penjelasanWajib && ! this.penjelasanTerisi) return false;

                    return this.buktiLengkap;
                },

                pilihAlasan(kode) {
                    this.alasan = kode;
                    // Penyelesaian yang tidak berlaku untuk alasan baru dilepas,
                    // supaya tidak terkirim diam-diam lalu ditolak server.
                    if (!this.penyelesaianTersedia.includes(this.penyelesaian)) {
                        this.penyelesaian = '';
                    }
                },

                tutup() { this.buka = false; },

                init() {
                    this.$watch('buka', (nilai) => {
                        document.body.style.overflow = nilai ? 'hidden' : '';
                    });
                },
                destroy() { document.body.style.overflow = ''; }
             }"
             @buka-retur.window="buka = true"
             @keydown.escape.window="tutup()"
             x-show="buka"
             x-cloak
             class="batal-lapis">

            <div class="batal-kotak" @click.outside="tutup()">

                <div class="batal-kepala">
                    <div>
                        <h3 class="batal-judul">Ajukan Pengembalian</h3>
                        <p class="batal-nomor">{{ $order->order_number }}</p>
                    </div>
                    <button type="button" @click="tutup()" class="batal-tutup" aria-label="Tutup">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- enctype wajib: tanpa ini berkasnya tidak ikut terkirim sama
                     sekali dan server hanya melihat isian teksnya. --}}
                <form action="{{ route('orders.return', $order->order_number) }}" method="POST"
                      enctype="multipart/form-data" class="batal-form">
                    @csrf

                    <div class="batal-isi">
                        <p class="batal-tanya">Apa yang bermasalah dengan pesanannya?</p>

                        <div class="space-y-2">
                            @foreach($pilihanRetur as $pilihan)
                            <label class="retur-pilihan"
                                   :class="alasan === '{{ $pilihan['kode'] }}' && 'retur-pilihan-aktif'">
                                <input type="radio" name="reason_code" value="{{ $pilihan['kode'] }}"
                                       class="sr-only" x-model="alasan"
                                       @change="pilihAlasan('{{ $pilihan['kode'] }}')">
                                <span class="retur-radio">
                                    <span class="retur-radio-titik" x-show="alasan === '{{ $pilihan['kode'] }}'" x-cloak></span>
                                </span>
                                <span class="retur-pilihan-teks">
                                    <span class="retur-pilihan-label">{{ $pilihan['label'] }}</span>
                                    <span class="retur-pilihan-ket">{{ $pilihan['keterangan'] }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('reason_code') <p class="galat-retur">{{ $message }}</p> @enderror

                        {{-- Penyelesaian mengikuti alasan yang dipilih --}}
                        <div x-show="alasan" x-cloak class="retur-blok">
                            <p class="batal-tanya">Mau diselesaikan bagaimana?</p>

                            <div class="retur-selesai-baris">
                                <template x-if="penyelesaianTersedia.includes('exchange')">
                                    <label class="retur-selesai" :class="penyelesaian === 'exchange' && 'retur-selesai-aktif'">
                                        <input type="radio" name="resolution" value="exchange" class="sr-only" x-model="penyelesaian">
                                        <i class="fa-solid fa-right-left"></i>
                                        <span class="retur-selesai-label">Tukar Barang / Ukuran</span>
                                        <span class="retur-selesai-ket">Ditukar dengan ukuran lain, warna lain, atau barang pengganti</span>
                                    </label>
                                </template>

                                <template x-if="penyelesaianTersedia.includes('refund')">
                                    <label class="retur-selesai" :class="penyelesaian === 'refund' && 'retur-selesai-aktif'">
                                        <input type="radio" name="resolution" value="refund" class="sr-only" x-model="penyelesaian">
                                        <i class="fa-solid fa-wallet"></i>
                                        <span class="retur-selesai-label">Pengembalian Dana</span>
                                        <span class="retur-selesai-ket">Dana masuk ke R_Pay, bisa dipakai belanja atau dicairkan ke bank</span>
                                    </label>
                                </template>
                            </div>
                            @error('resolution') <p class="galat-retur">{{ $message }}</p> @enderror
                        </div>

                        {{-- Ukuran pengganti, hanya bila menukar --}}
                        <div x-show="penyelesaian === 'exchange'" x-cloak class="retur-blok">
                            <label class="batal-tanya">Barang atau ukuran pengganti yang kamu inginkan</label>
                            <input type="text" name="exchange_request" value="{{ old('exchange_request') }}"
                                   maxlength="255"
                                   placeholder="Contoh: ukuran 43, atau warna hitam ukuran 42"
                                   class="retur-input">
                            <p class="retur-bantu">
                                Boleh menukar ukuran, warna, atau model lain selama harganya sama.
                                Barang yang kamu pesan: {{ $ukuranDipesan->implode(', ') ?: '—' }}
                            </p>
                            @error('exchange_request') <p class="galat-retur">{{ $message }}</p> @enderror
                        </div>

                        {{-- Penjelasan bebas. Opsional untuk alasan yang sudah
                             jelas dari daftar, wajib untuk "Alasan lain". --}}
                        <div class="retur-blok">
                            <label class="batal-tanya">
                                Ceritakan lebih jelas
                                <span class="retur-wajib" x-show="penjelasanWajib" x-cloak>wajib diisi</span>
                                <span class="retur-opsional" x-show="!penjelasanWajib" x-cloak>opsional</span>
                            </label>

                            <textarea name="reason" rows="4" x-model="penjelasan"
                                      :placeholder="penjelasanWajib
                                          ? 'Tuliskan alasanmu di sini, minimal ' + minimalPenjelasan + ' karakter.'
                                          : 'Boleh dikosongkan. Isi kalau ada hal yang perlu kami tahu.'"
                                      class="retur-input"></textarea>

                            <p class="retur-bantu" x-show="penjelasanWajib" x-cloak>
                                Karena kamu memilih "Alasan lain", kolom ini wajib diisi —
                                minimal {{ config('alasan-retur.minimal_penjelasan', 15) }} karakter.
                            </p>
                            <p class="retur-bantu" x-show="!penjelasanWajib" x-cloak>
                                Tidak wajib. Alasan yang kamu pilih di atas sudah cukup buat kami,
                                tapi kalau diisi kami bisa memprosesnya lebih cepat.
                            </p>

                            @error('reason') <p class="galat-retur">{{ $message }}</p> @enderror
                        </div>

                        {{-- ── Bukti wajib ── --}}
                        <div x-show="alasan" x-cloak class="retur-blok">
                            <label class="batal-tanya">
                                Lampirkan bukti
                                <span class="retur-wajib">wajib semua</span>
                            </label>

                            <p class="retur-bantu bukti-pengantar">
                                Ketiganya kami perlukan untuk memastikan barang yang kamu ajukan
                                memang barang yang kami kirim, dan kondisinya terekam sejak
                                paketnya pertama kali dibuka. Tanpa ini pengajuan tidak bisa diproses.
                            </p>

                            <div class="bukti-daftar" :data-sibuk="sedangMemampatkan ? '1' : '0'">
                                @php
                                    $kotakBukti = [
                                        ['kunci' => 'receipt_photo', 'ikon' => 'fa-receipt',
                                         'judul' => 'Foto Resi',
                                         'ket'   => 'Label pengiriman pada paket, nomor resinya harus terbaca jelas.'],
                                        ['kunci' => 'package_photo', 'ikon' => 'fa-box-open',
                                         'judul' => 'Foto Paket',
                                         'ket'   => 'Keadaan paket saat diterima, tampak seluruhnya.'],
                                    ];
                                @endphp

                                @foreach($kotakBukti as $kotak)
                                    <label class="bukti-kotak"
                                           :class="{
                                               'bukti-kotak-terisi': bukti.{{ $kotak['kunci'] }},
                                               'bukti-kotak-sibuk': sedangProses === '{{ $kotak['kunci'] }}',
                                           }">
                                        <input type="file" name="{{ $kotak['kunci'] }}"
                                               accept="image/jpeg,image/png,image/webp"
                                               @change="pilihFoto($event, '{{ $kotak['kunci'] }}')"
                                               class="bukti-masukan">

                                        <span class="bukti-ikon">
                                            <i class="fa-solid {{ $kotak['ikon'] }}"
                                               x-show="!bukti.{{ $kotak['kunci'] }} && sedangProses !== '{{ $kotak['kunci'] }}'"></i>
                                            <i class="fa-solid fa-spinner bukti-putar"
                                               x-show="sedangProses === '{{ $kotak['kunci'] }}'" x-cloak></i>
                                            <i class="fa-solid fa-circle-check"
                                               x-show="bukti.{{ $kotak['kunci'] }} && sedangProses !== '{{ $kotak['kunci'] }}'" x-cloak></i>
                                        </span>

                                        <span class="bukti-teks">
                                            <span class="bukti-judul">{{ $kotak['judul'] }}</span>
                                            <span class="bukti-ket"
                                                  x-show="!bukti.{{ $kotak['kunci'] }} && sedangProses !== '{{ $kotak['kunci'] }}'">
                                                {{ $kotak['ket'] }}
                                            </span>
                                            <span class="bukti-proses" x-show="sedangProses === '{{ $kotak['kunci'] }}'" x-cloak>
                                                Mengecilkan fotonya…
                                            </span>
                                            <span class="bukti-nama" x-show="bukti.{{ $kotak['kunci'] }}" x-cloak
                                                  x-text="bukti.{{ $kotak['kunci'] }}"></span>
                                            {{-- Hasil pemampatan disebutkan angkanya. Pembeli berhak
                                                 tahu berkasnya diubah, bukan diam-diam diganti. --}}
                                            <span class="bukti-susut" x-show="susutBukti.{{ $kotak['kunci'] }}" x-cloak>
                                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                <span x-text="'Dikecilkan otomatis: ' + susutBukti.{{ $kotak['kunci'] }}"></span>
                                            </span>
                                        </span>

                                        <span class="bukti-aksi"
                                              x-text="sedangProses === '{{ $kotak['kunci'] }}'
                                                  ? 'Memproses…'
                                                  : (bukti.{{ $kotak['kunci'] }} ? 'Ganti' : 'Pilih Foto')"></span>
                                    </label>

                                    <p class="galat-retur" x-show="galatBukti.{{ $kotak['kunci'] }}" x-cloak
                                       x-text="galatBukti.{{ $kotak['kunci'] }}"></p>
                                    @error($kotak['kunci']) <p class="galat-retur">{{ $message }}</p> @enderror
                                @endforeach

                                <label class="bukti-kotak"
                                       :class="{
                                           'bukti-kotak-terisi': bukti.unboxing_video,
                                           'bukti-kotak-sibuk': sedangProses === 'unboxing_video',
                                       }">
                                    <input type="file" name="unboxing_video"
                                           accept="video/mp4,video/quicktime,video/webm"
                                           @change="pilihVideo($event)"
                                           class="bukti-masukan">

                                    <span class="bukti-ikon">
                                        <i class="fa-solid fa-video"
                                           x-show="!bukti.unboxing_video && sedangProses !== 'unboxing_video'"></i>
                                        <i class="fa-solid fa-spinner bukti-putar"
                                           x-show="sedangProses === 'unboxing_video'" x-cloak></i>
                                        <i class="fa-solid fa-circle-check"
                                           x-show="bukti.unboxing_video && sedangProses !== 'unboxing_video'" x-cloak></i>
                                    </span>

                                    <span class="bukti-teks">
                                        <span class="bukti-judul">
                                            Video Unboxing
                                            <span class="bukti-batas">maks {{ (int) config('alasan-retur.bukti.maks_durasi_detik', 120) / 60 }} menit</span>
                                        </span>
                                        <span class="bukti-ket"
                                              x-show="!bukti.unboxing_video && sedangProses !== 'unboxing_video'">
                                            Rekaman utuh tanpa potongan, sejak paket masih tersegel sampai barangnya terlihat.
                                        </span>

                                        {{-- Penyandian ulang berjalan dalam waktu nyata, jadi --}}
                                        <span class="bukti-proses" x-show="sedangProses === 'unboxing_video'" x-cloak>
                                            <span x-text="'Mengecilkan videonya… ' + kemajuan + '%'"></span>
                                            <span class="bukti-bilah">
                                                <span class="bukti-bilah-isi" :style="'width:' + kemajuan + '%'"></span>
                                            </span>
                                            <span class="bukti-proses-ket">
                                                Prosesnya memutar videonya sampai habis, jadi lamanya
                                                kira-kira sepanjang videonya. Jangan tutup halaman ini.
                                            </span>
                                        </span>

                                        <span class="bukti-nama" x-show="bukti.unboxing_video" x-cloak>
                                            <span x-text="bukti.unboxing_video"></span>
                                            <template x-if="durasiVideo">
                                                <span x-text="' — ' + jam(durasiVideo)"></span>
                                            </template>
                                        </span>
                                        <span class="bukti-susut" x-show="susutBukti.unboxing_video" x-cloak>
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                            <span x-text="'Dikecilkan otomatis: ' + susutBukti.unboxing_video"></span>
                                        </span>
                                    </span>

                                    <span class="bukti-aksi"
                                          x-text="sedangProses === 'unboxing_video'
                                              ? 'Memproses…'
                                              : (bukti.unboxing_video ? 'Ganti' : 'Pilih Video')"></span>
                                </label>

                                <p class="galat-retur" x-show="galatBukti.unboxing_video" x-cloak
                                   x-text="galatBukti.unboxing_video"></p>
                                @error('unboxing_video') <p class="galat-retur">{{ $message }}</p> @enderror
                            </div>

                            {{-- Durasi hasil pembacaan di peramban ikut dikirim supaya --}}
                            <input type="hidden" name="video_duration" :value="durasiVideo ?? ''">

                            <p class="retur-bantu">
                                Foto di atas {{ (int) config('alasan-retur.bukti.maks_foto_mb', 2) }} MB dan video
                                di atas {{ (int) config('alasan-retur.bukti.maks_video_mb', 10) }} MB otomatis
                                dikecilkan di sini — tidak perlu kamu urus sendiri.
                                Yang tidak bisa dikecilkan hanyalah durasinya: video di atas
                                {{ (int) config('alasan-retur.bukti.maks_durasi_detik', 120) / 60 }} menit tetap ditolak.
                                Kalau rekamanmu lebih panjang, potong bagian setelah barangnya terlihat —
                                bagian awalnya justru yang penting.
                            </p>
                        </div>

                        <div class="retur-catatan">
                            <i class="fa-solid fa-circle-info"></i>
                            <p>
                                Pengajuan akan ditinjau admin. Kalau disetujui dan kamu memilih pengembalian dana,
                                saldonya langsung masuk ke R_Pay dan bisa dipakai belanja atau dicairkan ke rekening bankmu.
                            </p>
                        </div>
                    </div>

                    <div class="retur-kaki">
                        {{-- Bila tombol kirim masih mati, sebutkan apa yang kurang. --}}
                        <p class="retur-kaki-sisa" x-show="!siapKirim" x-cloak>
                            <i class="fa-solid fa-circle-info"></i>
                            <span x-text="!alasan
                                ? 'Pilih dulu alasannya di atas'
                                : (!penyelesaian
                                    ? 'Pilih dulu cara penyelesaiannya'
                                    : ((penjelasanWajib && !penjelasanTerisi)
                                        ? 'Tuliskan dulu alasanmu, minimal ' + minimalPenjelasan + ' karakter'
                                        : (!bukti.receipt_photo
                                            ? 'Lampirkan foto resinya'
                                            : (!bukti.package_photo
                                                ? 'Lampirkan foto paketnya'
                                                : 'Lampirkan video unboxing-nya'))))"></span>
                        </p>

                        <p class="retur-kaki-siap" x-show="siapKirim" x-cloak>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Siap dikirim untuk ditinjau admin</span>
                        </p>

                        <div class="retur-kaki-tombol">
                            <button type="button" @click="tutup()" class="retur-tombol retur-tombol-nanti">
                                Nanti Saja
                            </button>

                            <button type="submit" class="retur-tombol retur-tombol-kirim"
                                    :disabled="!siapKirim">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Kirim Pengajuan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

    @push('scripts')
    {{-- Pemampat berkas bukti. --}}
    <script src="{{ asset('js/pemampat-berkas.js') }}?v={{ filemtime(public_path('js/pemampat-berkas.js')) }}"></script>
    @endpush

    @push('styles')
    <style>
        /* ── Modal pengembalian ───────────────────────────────────── Kerangka modalnya memakai kembali .ba... */
        /* Selama ada berkas diproses, kotak lain tidak bisa ditekan: dua pemampatan sekaligus akan berebut ... */
        .bukti-daftar[data-sibuk="1"] .bukti-kotak { pointer-events: none; opacity: .55; }
        .bukti-daftar[data-sibuk="1"] .bukti-kotak-sibuk { pointer-events: none; opacity: 1; }

        .bukti-putar { animation: bukti-putar 900ms linear infinite; }
        @keyframes bukti-putar { to { transform: rotate(360deg); } }

        .bukti-proses {
            display: block; margin-top: 3px;
            font-size: 12px; font-weight: 600; color: #b45309;
        }
        .bukti-proses-ket {
            display: block; margin-top: 3px;
            font-size: 11px; font-weight: 400; line-height: 1.5; color: #92400e;
        }

        .bukti-bilah {
            display: block; margin-top: 5px;
            height: 5px; border-radius: 999px; background: #fde68a; overflow: hidden;
        }
        .bukti-bilah-isi {
            display: block; height: 100%; width: 0;
            background: #f59e0b; border-radius: 999px;
            transition: width 300ms ease;
        }

        .bukti-susut {
            display: block; margin-top: 3px;
            font-size: 11px; font-weight: 600; color: #0369a1;
        }
        .bukti-susut i { margin-right: 4px; }

        /* ── Kotak unggah bukti ── Ditulis tangan, sebab CSS bawaan yang sudah dibangun tidak memuat sebagi... */
        .bukti-pengantar { margin-bottom: 12px; }

        .bukti-daftar { display: flex; flex-direction: column; gap: 10px; }

        /* Masukan berkas aslinya disembunyikan, bukan dihapus: seluruh kotak berfungsi sebagai <label>-nya,... */
        .bukti-masukan {
            position: absolute; width: 1px; height: 1px;
            opacity: 0; overflow: hidden; clip: rect(0 0 0 0);
        }

        .bukti-kotak {
            position: relative;
            display: flex; align-items: center; gap: 12px;
            padding: 13px 15px;
            border: 1.5px dashed #d1d5db; border-radius: 12px;
            background: #fafafa; cursor: pointer;
            transition: border-color 160ms ease, background-color 160ms ease;
        }
        .bukti-kotak:hover { border-color: #9ca3af; background: #f5f5f5; }

        .bukti-kotak-terisi {
            border-style: solid; border-color: #16a34a; background: #f0fdf4;
        }

        .bukti-ikon {
            flex: none;
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 10px;
            background: #eef2f7; color: #4b5563; font-size: 16px;
        }
        .bukti-kotak-terisi .bukti-ikon { background: #dcfce7; color: #16a34a; }

        .bukti-teks { flex: 1 1 auto; min-width: 0; }

        .bukti-judul {
            display: block; font-size: 14px; font-weight: 700; color: #111827;
        }
        .bukti-batas {
            display: inline-block; margin-left: 6px;
            padding: 1px 7px; border-radius: 999px;
            background: #fef3c7; color: #92400e;
            font-size: 11px; font-weight: 700;
        }
        .bukti-ket {
            display: block; margin-top: 2px;
            font-size: 12px; line-height: 1.5; color: #6b7280;
        }
        /* Nama berkas dari ponsel bisa sangat panjang; dipotong daripada mendorong tombol "Ganti" keluar da... */
        .bukti-nama {
            display: block; margin-top: 2px;
            font-size: 12px; color: #15803d; font-weight: 600;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .bukti-aksi {
            flex: none;
            padding: 7px 13px; border-radius: 9px;
            background: #111827; color: #fff;
            font-size: 12px; font-weight: 700;
        }
        .bukti-kotak-terisi .bukti-aksi { background: #16a34a; }

        @media (max-width: 480px) {
            .bukti-kotak { flex-wrap: wrap; }
            .bukti-aksi  { width: 100%; text-align: center; }
        }

        .retur-pilihan {
            display: flex; gap: 11px; align-items: flex-start;
            padding: 12px 14px; border: 1.5px solid #e5e7eb;
            border-radius: 12px; background: #fff; cursor: pointer;
            transition: border-color 160ms ease, background-color 160ms ease;
        }
        .retur-pilihan:hover { border-color: #cbd5e1; background: #f9fafb; }
        .retur-pilihan-aktif {
            border-color: #1B3A6B; background: #eff6ff;
            box-shadow: 0 0 0 3px rgb(27 58 107 / .08);
        }
        .retur-radio {
            width: 17px; height: 17px; border-radius: 50%;
            border: 2px solid #cbd5e1; flex-shrink: 0; margin-top: 1px;
            display: flex; align-items: center; justify-content: center;
        }
        .retur-pilihan-aktif .retur-radio { border-color: #1B3A6B; }
        .retur-radio-titik { width: 8px; height: 8px; border-radius: 50%; background: #1B3A6B; }
        .retur-pilihan-teks { display: flex; flex-direction: column; gap: 2px; }
        .retur-pilihan-label { font-size: 12.5px; font-weight: 700; color: #374151; }
        .retur-pilihan-ket { font-size: 10.5px; color: #9ca3af; line-height: 1.5; }

        .retur-blok { margin-top: 18px; }

        .retur-selesai-baris { display: grid; gap: 10px; }
        @media (min-width: 640px) { .retur-selesai-baris { grid-template-columns: 1fr 1fr; } }

        .retur-selesai {
            display: flex; flex-direction: column; gap: 3px;
            padding: 14px; border: 1.5px solid #e5e7eb; border-radius: 12px;
            background: #fff; cursor: pointer;
            transition: border-color 160ms ease, background-color 160ms ease;
        }
        .retur-selesai:hover { border-color: #cbd5e1; background: #f9fafb; }
        .retur-selesai-aktif {
            border-color: #1B3A6B; background: #eff6ff;
            box-shadow: 0 0 0 3px rgb(27 58 107 / .08);
        }
        .retur-selesai i { font-size: 15px; color: #1B3A6B; margin-bottom: 3px; }
        .retur-selesai-label { font-size: 12.5px; font-weight: 800; color: #374151; }
        .retur-selesai-ket { font-size: 10.5px; color: #9ca3af; line-height: 1.5; }

        .retur-input {
            width: 100%; font-size: 12px; background: #fff;
            border: 1px solid #e5e7eb; border-radius: 10px;
            padding: 10px 12px; margin-top: 8px;
        }
        .retur-input:focus {
            outline: none; border-color: #1B3A6B;
            box-shadow: 0 0 0 3px rgb(27 58 107 / .1);
        }
        .retur-bantu { font-size: 10px; color: #9ca3af; margin-top: 5px; line-height: 1.5; }

        /* Penanda wajib/opsional pada label penjelasan */
        .retur-wajib,
        .retur-opsional {
            display: inline-block; margin-left: 7px;
            padding: 2px 7px; border-radius: 999px;
            font-size: 9px; font-weight: 800;
            letter-spacing: .04em; text-transform: uppercase;
            vertical-align: middle;
        }
        .retur-wajib { background: #fef2f2; color: #b91c1c; }
        .retur-opsional { background: #f3f4f6; color: #6b7280; }
        .galat-retur { font-size: 10.5px; color: #dc2626; font-weight: 600; margin-top: 6px; }

        .retur-catatan {
            display: flex; gap: 9px; align-items: flex-start;
            background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px;
            padding: 12px 14px; margin-top: 18px;
            font-size: 11px; color: #1e40af; line-height: 1.6;
        }
        .retur-catatan i { margin-top: 2px; }

        /* ══ Modal konfirmasi penerimaan ═══════════════════════════ Isinya panjang karena memuat syarat ga... */
        .terima-kotak {
            background: #fff; border-radius: 18px;
            box-shadow: 0 22px 50px rgb(0 0 0 / .28);
            width: 100%; max-width: 440px;
            max-height: 88vh;
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .terima-kepala {
            flex-shrink: 0; text-align: center;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #f3f4f6;
        }
        .terima-ikon {
            width: 56px; height: 56px; margin: 0 auto 12px;
            border-radius: 999px; background: #ecfdf5; color: #059669;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .terima-judul { font-size: 15px; font-weight: 900; color: #111827; }
        .terima-sub { font-size: 11.5px; color: #6b7280; line-height: 1.6; margin-top: 6px; }

        .terima-isi {
            flex: 1 1 auto; min-height: 0; overflow-y: auto;
            overscroll-behavior: contain;
            padding: 18px 24px; display: grid; gap: 14px;
        }

        .terima-garansi {
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 12px; padding: 15px;
        }
        .terima-garansi-judul {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 900; color: #92400e;
        }
        .terima-garansi-teks {
            font-size: 11.5px; color: #92400e; line-height: 1.7; margin-top: 8px;
        }
        .terima-garansi-daftar { list-style: none; margin: 11px 0 0; padding: 0; }
        .terima-garansi-daftar li {
            display: flex; gap: 8px; align-items: flex-start;
            font-size: 11px; color: #a16207; line-height: 1.6; margin-bottom: 5px;
        }
        .terima-garansi-daftar i { margin-top: 3px; font-size: 8px; flex-shrink: 0; }
        .terima-garansi-tegas {
            display: flex; gap: 8px; align-items: flex-start;
            margin-top: 12px; padding-top: 11px;
            border-top: 1px solid #fde68a;
            font-size: 11.5px; font-weight: 700; color: #b45309; line-height: 1.6;
        }

        .terima-catatan {
            display: flex; gap: 9px; align-items: flex-start;
            background: #eff6ff; border: 1px solid #dbeafe; border-radius: 12px;
            padding: 12px 14px; font-size: 11px; color: #1e40af; line-height: 1.65;
        }
        .terima-catatan i { margin-top: 2px; flex-shrink: 0; }

        .terima-kaki {
            flex-shrink: 0; display: flex; gap: 10px;
            padding: 16px 24px; border-top: 1px solid #f3f4f6; background: #fafbfc;
        }
        .terima-tombol {
            flex: 1 1 0; display: inline-flex; align-items: center; justify-content: center;
            gap: 7px; padding: 12px; border-radius: 10px;
            font-size: 12px; font-weight: 800; transition: background-color 160ms ease;
        }
        .terima-tombol-batal {
            background: #fff; color: #6b7280; border: 1px solid #e5e7eb;
        }
        .terima-tombol-batal:hover { background: #f3f4f6; color: #374151; }
        .terima-tombol-ya {
            background: #059669; color: #fff; box-shadow: 0 2px 8px rgb(5 150 105 / .28);
        }
        .terima-tombol-ya:hover { background: #047857; }

        /* ══ Pesanan selesai: pengembalian & kode referal ═══════════ */
        .selesai-retur {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 16px;
        }
        .selesai-retur-tombol {
            width: 100%; display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px; border-radius: 10px;
            background: #fff; border: 1.5px solid #fecaca; color: #b91c1c;
            font-size: 12px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .04em;
            transition: border-color 160ms ease, background-color 160ms ease;
        }
        .selesai-retur-tombol:hover { border-color: #f87171; background: #fef2f2; }
        .selesai-retur-ket {
            font-size: 10.5px; color: #6b7280; line-height: 1.65;
            text-align: center; margin-top: 10px;
        }

        .selesai-lewat {
            display: flex; gap: 10px; align-items: flex-start;
            background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 12px;
            padding: 14px 16px; font-size: 11px; color: #6b7280; line-height: 1.65;
        }
        .selesai-lewat i { margin-top: 2px; color: #9ca3af; flex-shrink: 0; }

        /* ── Kartu kode referal pada pesanan selesai ── */
        .referal-pesanan {
            border-radius: 16px; padding: 20px;
            background: linear-gradient(135deg, #f0f9ff 0%, #eef7fe 100%);
            border: 1px solid #bfdbfe;
        }
        .referal-pesanan-kepala { display: flex; gap: 13px; align-items: flex-start; }
        .referal-pesanan-ikon {
            width: 42px; height: 42px; border-radius: 999px; flex-shrink: 0;
            background: #2563eb; color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
            box-shadow: 0 2px 8px rgb(37 99 235 / .35);
        }
        .referal-pesanan-judul { font-size: 13px; font-weight: 900; color: #1e3a8a; }
        .referal-pesanan-sub { font-size: 11px; color: #1d4ed8; line-height: 1.6; margin-top: 2px; }

        .referal-pesanan-kotak {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-top: 15px; padding: 14px 16px;
            background: #fff; border: 1.5px dashed #93c5fd; border-radius: 12px;
        }
        .referal-pesanan-kode {
            flex: 1 1 auto; min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 16px; font-weight: 900; letter-spacing: .04em;
            color: #1e3a8a; word-break: break-all;
        }
        .referal-pesanan-salin {
            flex-shrink: 0; background: #2563eb; color: #fff;
            padding: 8px 14px; border-radius: 8px;
            font-size: 11px; font-weight: 800;
            transition: background-color 160ms ease;
        }
        .referal-pesanan-salin:hover { background: #1d4ed8; }

        .referal-pesanan-untung {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
            margin-top: 14px;
        }
        .referal-pesanan-untung > div {
            background: #fff; border: 1px solid #dbeafe;
            border-radius: 10px; padding: 12px; text-align: center;
        }
        .referal-pesanan-untung-nilai { font-size: 19px; font-weight: 900; color: #2563eb; }
        .referal-pesanan-untung-label {
            font-size: 9.5px; font-weight: 800; letter-spacing: .04em;
            text-transform: uppercase; color: #93a3b8; margin-top: 2px;
        }

        .referal-pesanan-ingat {
            display: flex; gap: 9px; align-items: flex-start;
            margin-top: 14px; padding: 12px 14px;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
            font-size: 10.5px; color: #92400e; line-height: 1.7;
        }
        .referal-pesanan-ingat i { margin-top: 2px; flex-shrink: 0; }

        /* ── Keterangan & keadaan pembatalan ──────────────────────── */
        .batal-keterangan {
            display: flex; gap: 8px; align-items: flex-start;
            margin-top: 10px; font-size: 11px; line-height: 1.65; color: #6b7280;
        }
        .batal-keterangan i { margin-top: 2px; flex-shrink: 0; color: #9ca3af; }

        .batal-tunggu,
        .batal-ditolak {
            display: flex; gap: 12px; align-items: flex-start;
            border-radius: 12px; padding: 16px; border: 1px solid;
        }
        .batal-tunggu  { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .batal-ditolak { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .batal-tunggu > i, .batal-ditolak > i { margin-top: 2px; font-size: 15px; flex-shrink: 0; }
        .batal-tunggu-judul { font-size: 12.5px; font-weight: 800; }
        .batal-tunggu-teks  { font-size: 11.5px; line-height: 1.7; margin-top: 4px; }

        /* ── Ringkasan galat validasi ─────────────────────────────── */
        .galat-ringkas {
            display: flex; gap: 12px; align-items: flex-start;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 4px; padding: 16px 18px;
        }
        .galat-ringkas > i { color: #dc2626; font-size: 16px; margin-top: 1px; flex-shrink: 0; }
        .galat-ringkas-judul {
            font-size: 12.5px; font-weight: 800; color: #991b1b;
        }
        .galat-ringkas-daftar {
            list-style: disc; margin: 7px 0 0; padding-left: 17px;
            font-size: 11.5px; color: #b91c1c; line-height: 1.7;
        }
        .galat-ringkas-bantu {
            font-size: 10.5px; color: #ef4444; margin-top: 8px; line-height: 1.6;
        }

        /* ── Kaki modal & tombolnya ───────────────────────────────── flex-shrink: 0 menjaga tombol tetap t... */
        .retur-kaki {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            background: linear-gradient(to bottom, #fbfcfd, #f6f7f9);
        }
        @media (min-width: 560px) {
            .retur-kaki { flex-direction: row; align-items: center; justify-content: space-between; }
        }

        .retur-kaki-sisa,
        .retur-kaki-siap {
            display: flex; align-items: center; gap: 7px;
            font-size: 11px; font-weight: 600; line-height: 1.4;
        }
        .retur-kaki-sisa { color: #b45309; }
        .retur-kaki-siap { color: #047857; }
        .retur-kaki-sisa i,
        .retur-kaki-siap i { font-size: 12px; flex-shrink: 0; }

        .retur-kaki-tombol {
            display: flex; gap: 10px;
            flex-direction: column-reverse;
        }
        @media (min-width: 560px) {
            .retur-kaki-tombol { flex-direction: row; margin-left: auto; }
        }

        .retur-tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: .01em;
            border-radius: 10px;
            border: 1.5px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: background-color 180ms ease, border-color 180ms ease,
                        box-shadow 180ms ease, transform 120ms ease;
        }
        .retur-tombol:active { transform: translateY(1px); }
        .retur-tombol:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgb(27 58 107 / .25);
        }

        .retur-tombol-nanti {
            background: #fff;
            color: #6b7280;
            border-color: #e5e7eb;
        }
        .retur-tombol-nanti:hover { background: #f3f4f6; border-color: #d1d5db; color: #374151; }

        .retur-tombol-kirim {
            background: var(--color-primary, #1B3A6B);
            color: #fff;
            box-shadow: 0 2px 8px rgb(27 58 107 / .22);
        }
        .retur-tombol-kirim:hover:not(:disabled) {
            background: #2d5a9e;
            box-shadow: 0 4px 14px rgb(27 58 107 / .3);
        }

        /* Keadaan mati dibuat jelas terbaca sebagai "belum bisa", bukan sekadar tombol pudar yang menyisaka... */
        .retur-tombol-kirim:disabled {
            background: #e5e7eb;
            color: #9ca3af;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }
        .retur-tombol i { font-size: 11px; }

        /* ══ Timeline pengembalian ══════════════════════════════════ */
        .retur-jalur {
            border-top: 1px solid #e5e7eb;
            margin-top: 6px;
        }
        .retur-jalur-kepala {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; flex-wrap: wrap; margin-bottom: 18px;
        }
        .retur-jalur-nomor {
            margin-top: 2px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px; font-weight: 700; letter-spacing: .02em;
            color: #1B3A6B;
        }

        .retur-jalur-judul {
            font-size: 12px; font-weight: 900; color: #1B3A6B;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .retur-jalur-sub { font-size: 11px; color: #9ca3af; margin-top: 3px; }

        .retur-lencana {
            padding: 5px 11px; border-radius: 999px;
            font-size: 10px; font-weight: 800; white-space: nowrap;
            border: 1px solid;
        }
        .retur-lencana-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .retur-lencana-info    { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .retur-lencana-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .retur-lencana-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        /* Garis penghubung digambar dari titik langkah, bukan elemen sendiri, supaya panjangnya selalu pas ... */
        .retur-langkah { list-style: none; margin: 0; padding: 0; }
        .retur-langkah-item {
            position: relative;
            display: flex; gap: 14px;
            padding-bottom: 20px;
        }
        .retur-langkah-item:last-child { padding-bottom: 0; }
        .retur-langkah-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 15px; top: 32px; bottom: 4px;
            width: 2px; background: #e5e7eb;
        }
        .retur-langkah-selesai:not(:last-child)::before { background: #10b981; }

        .retur-titik {
            position: relative; z-index: 1; flex-shrink: 0;
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; border: 2px solid;
        }
        .retur-langkah-selesai .retur-titik {
            background: #10b981; border-color: #10b981; color: #fff;
        }
        .retur-langkah-sekarang .retur-titik {
            background: #fff; border-color: #1B3A6B; color: #1B3A6B;
            box-shadow: 0 0 0 4px rgb(27 58 107 / .12);
        }
        .retur-langkah-menunggu .retur-titik {
            background: #f9fafb; border-color: #e5e7eb; color: #d1d5db;
        }

        .retur-langkah-teks { padding-top: 4px; min-width: 0; }
        .retur-langkah-judul { font-size: 12.5px; font-weight: 800; color: #374151; }
        .retur-langkah-ket { font-size: 11px; color: #6b7280; line-height: 1.6; margin-top: 2px; }
        .retur-langkah-waktu { font-size: 10px; color: #9ca3af; margin-top: 4px; }
        .retur-langkah-menunggu .retur-langkah-judul { color: #9ca3af; }
        .retur-langkah-menunggu .retur-langkah-ket { color: #b8bec7; }
        .retur-langkah-sekarang .retur-langkah-judul { color: #1B3A6B; }

        /* ── Giliran pembeli mengirim barang ── */
        .retur-kirim {
            margin-top: 18px; padding: 18px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
        }
        .retur-kirim-judul {
            font-size: 12px; font-weight: 900; color: #1B3A6B;
            display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
        }

        .retur-alamat {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
            padding: 14px; font-size: 11.5px; color: #4b5563; line-height: 1.7;
        }
        .retur-alamat-nama { font-weight: 800; color: #1f2937; }

        .retur-ongkir {
            display: flex; gap: 9px; align-items: flex-start;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
            padding: 12px 14px; margin-top: 12px;
            font-size: 11px; color: #92400e; line-height: 1.6;
        }
        .retur-ongkir i { margin-top: 2px; flex-shrink: 0; }

        .retur-syarat {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
            padding: 12px 14px; margin-top: 12px;
        }
        .retur-syarat-judul { font-size: 11.5px; font-weight: 800; color: #991b1b; }
        .retur-syarat-ket { font-size: 11px; color: #b91c1c; line-height: 1.6; margin-top: 4px; }
        .retur-syarat-daftar { list-style: none; margin: 9px 0 0; padding: 0; }
        .retur-syarat-daftar li {
            display: flex; gap: 7px; align-items: flex-start;
            font-size: 10.5px; color: #7f1d1d; line-height: 1.6; margin-bottom: 4px;
        }
        .retur-syarat-daftar i { margin-top: 3px; font-size: 8px; flex-shrink: 0; }

        .retur-tenggat {
            font-size: 11px; color: #4b5563; margin-top: 12px; line-height: 1.6;
        }

        .retur-resi-form {
            margin-top: 16px; padding-top: 16px; border-top: 1px dashed #cbd5e1;
        }
        .retur-resi-judul { font-size: 11.5px; font-weight: 800; color: #374151; }
        .retur-resi-baris { display: grid; gap: 12px; margin-top: 10px; }
        @media (min-width: 560px) { .retur-resi-baris { grid-template-columns: 1fr 1.4fr; } }
        .retur-resi-label {
            display: block; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .05em; color: #6b7280;
        }
        .retur-resi-tombol { margin-top: 14px; width: 100%; }
        @media (min-width: 560px) { .retur-resi-tombol { width: auto; } }

        /* ── Resi yang sudah dicatat ── */
        .retur-resi-tercatat {
            margin-top: 16px; padding: 14px;
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
        }
        .retur-resi-tercatat-judul {
            font-size: 11px; font-weight: 800; color: #1d4ed8;
            display: flex; align-items: center; gap: 7px;
        }
        .retur-resi-tercatat-isi { font-size: 12px; color: #1e3a8a; margin-top: 5px; }
        .retur-resi-nomor {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 800; letter-spacing: .02em;
        }
        .retur-resi-tercatat-waktu { font-size: 10px; color: #3b82f6; margin-top: 3px; }

        /* ── Hasil akhir ── */
        .retur-selesai-kotak {
            margin-top: 16px; padding: 18px;
            background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px;
        }
        .retur-selesai-judul {
            font-size: 12px; font-weight: 900; color: #065f46;
            display: flex; align-items: center; gap: 8px;
        }
        .retur-selesai-nominal {
            font-size: 24px; font-weight: 900; color: #047857; margin-top: 6px;
        }
        .retur-selesai-ket { font-size: 11.5px; color: #047857; line-height: 1.6; margin-top: 3px; }

        /* ── Ditolak ── */
        .retur-tolak {
            padding: 18px; background: #fef2f2;
            border: 1px solid #fecaca; border-radius: 12px;
        }
        .retur-tolak-judul {
            font-size: 12px; font-weight: 900; color: #991b1b;
            display: flex; align-items: center; gap: 8px;
        }
        .retur-tolak-isi {
            font-size: 11.5px; color: #b91c1c; line-height: 1.7; margin-top: 8px;
        }
        .retur-tolak-bantu {
            font-size: 10.5px; color: #ef4444; line-height: 1.6;
            margin-top: 12px; padding-top: 12px; border-top: 1px solid #fecaca;
        }

        .retur-status-catatan {
            margin-top: 14px; padding: 12px 14px;
            background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
            font-size: 11.5px; color: #4b5563; line-height: 1.6;
        }
        .retur-status-tautan {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 12px; font-size: 11.5px; font-weight: 800;
            color: #047857; text-decoration: underline;
        }
    </style>
    @endpush
</x-app-layout>
