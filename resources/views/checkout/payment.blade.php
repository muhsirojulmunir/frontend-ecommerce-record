<x-app-layout>
    <x-slot name="title">Pembayaran Pesanan #{{ $order->order_number }}</x-slot>

    {{-- Midtrans Snap JS --}}
    @if($order->payment_method !== 'COD' && $snapToken)
        <script type="text/javascript"
                src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                data-client-key="{{ $clientKey }}"></script>
    @endif

    <style>
        @keyframes checkPop {
            0%   { transform: scale(0) rotate(-10deg); opacity: 0; }
            60%  { transform: scale(1.15) rotate(3deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.5); }
            50%       { box-shadow: 0 0 0 14px rgba(16,185,129,0); }
        }
        .check-pop   { animation: checkPop 0.55s cubic-bezier(.22,.68,0,1.2) forwards; }
        .fade-in-up  { animation: fadeInUp 0.5s ease forwards; }
        .pulse-green { animation: pulseGreen 1.5s ease-in-out infinite; }
    </style>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="paymentPage()"
         x-init="init()">

        {{-- ✅ Overlay Animasi Sukses --}}
        <div x-show="paymentSuccess"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
             style="display:none">
            <div class="bg-white rounded-2xl shadow-2xl p-10 max-w-sm w-full text-center space-y-5 fade-in-up">
                <div class="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center mx-auto pulse-green check-pop">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 mb-1">Pembayaran Berhasil!</h2>
                    <p class="text-sm text-gray-500">Pesanan Anda telah dikonfirmasi dan sedang diproses oleh toko.</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-800 font-medium">
                    🎉 Pesanan <strong>#{{ $order->order_number }}</strong> sudah LUNAS
                </div>
                <a href="{{ route('orders.show', $order->order_number) }}"
                   class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm py-3.5 rounded-xl transition uppercase tracking-wider">
                    Lihat Detail Pesanan →
                </a>
            </div>
        </div>

        <div class="bg-white border border-border rounded-sm shadow-sm overflow-hidden space-y-6 p-6 sm:p-8">

            {{-- Header Banner --}}
            <div :class="paymentSuccess ? 'bg-emerald-600' : 'bg-primary'"
                 class="text-white p-6 sm:p-8 rounded-sm text-center space-y-3 transition-colors duration-500">
                <div class="h-12 w-12 bg-white/10 rounded-full flex items-center justify-center mx-auto text-white text-xl">
                    <i x-show="!paymentSuccess" class="fa-solid fa-credit-card"></i>
                    <i x-show="paymentSuccess" class="fa-solid fa-circle-check"></i>
                </div>
                <h1 class="text-2xl font-black uppercase tracking-wider text-white">
                    <span x-show="!paymentSuccess">Instruksi Pembayaran</span>
                    <span x-show="paymentSuccess">Pembayaran Berhasil ✓</span>
                </h1>
                <p class="text-xs text-blue-100">
                    Nomor Pesanan: <strong class="text-white font-mono text-sm tracking-wide">#{{ $order->order_number }}</strong>
                </p>
                <div class="pt-2">
                    <div class="inline-block bg-white/10 border border-white/20 px-6 py-2.5 rounded-sm">
                        <span class="text-[10px] text-blue-200 uppercase font-bold tracking-widest block">Total Tagihan</span>
                        <span class="text-2xl font-black text-white tracking-tight">{{ $order->formatted_grand_total }}</span>
                    </div>
                </div>

                {{-- Status polling indicator --}}
                @if($snapError)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-sm p-4 text-xs flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5"></i>
                        <div>
                            <p class="font-bold text-amber-800">Sistem pembayaran otomatis sedang dalam antrean</p>
                            <p class="text-amber-700 mt-0.5">{{ $snapError }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.location.reload()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded text-[11px] shrink-0 transition">
                        Muat Ulang
                    </button>
                </div>
            @endif

            @if($order->payment_method !== 'COD')
                    <div x-show="!paymentSuccess" class="flex items-center justify-center gap-2 text-xs text-blue-200 pt-1">
                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <span>Menunggu konfirmasi pembayaran...</span>
                    </div>
                @endif
            </div>

            {{-- Ringkasan Pesanan --}}
            <div class="bg-gray-50 border border-border rounded-sm p-5 space-y-3 text-xs text-text">
                <div class="flex justify-between items-center border-b border-border pb-2">
                    <span class="text-text-light font-semibold">Metode Pembayaran</span>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-primary uppercase text-sm">{{ $order->payment_method }}</span>
                        @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                            <button type="button" @click="showChangePaymentModal = true; changeError = '';"
                                    class="bg-accent/10 hover:bg-accent/20 text-accent font-bold text-[11px] px-2.5 py-1 rounded-sm transition uppercase tracking-wider flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Ubah
                            </button>
                        @endif
                    </div>
                </div>
                <div class="flex justify-between items-center border-b border-border pb-2">
                    <span class="text-text-light font-semibold">Kurir Pengiriman</span>
                    <span class="font-bold text-text">{{ $order->courier }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-text-light font-semibold">Penerima Paket</span>
                    <span class="font-bold text-text text-right max-w-xs truncate">
                        {{ $order->shipping_address['recipient_name'] ?? '' }} ({{ $order->shipping_address['city'] ?? '' }})
                    </span>
                </div>
            </div>

            @if($snapError)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-sm p-4 text-xs flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5"></i>
                        <div>
                            <p class="font-bold text-amber-800">Sistem pembayaran otomatis sedang dalam antrean</p>
                            <p class="text-amber-700 mt-0.5">{{ $snapError }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.location.reload()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded text-[11px] shrink-0 transition">
                        Muat Ulang
                    </button>
                </div>
            @endif

            @if($order->payment_method !== 'COD')
                {{-- Konten Midtrans Snap --}}
                @if($snapToken)
                    <div class="bg-sky-50 border border-sky-200 text-sky-900 rounded-sm p-5 text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-sky-800 text-sm">
                            <i class="fa-solid fa-shield-halved text-sky-600"></i>
                            <span>Midtrans Payment Gateway</span>
                        </div>
                        <p class="leading-relaxed text-sky-800">
                            Jendela pembayaran otomatis akan terbuka. Selesaikan pembayaran, dan halaman ini akan otomatis terkonfirmasi.
                        </p>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="payWithMidtrans()"
                                :disabled="paying || paymentSuccess"
                                class="flex-1 bg-accent hover:bg-accent-dark text-white font-black text-xs py-4 rounded-sm text-center transition uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-lock"></i>
                            <span x-text="paying ? 'Membuka Jendela Pembayaran...' : 'Bayar Sekarang'"></span>
                        </button>
                        <a href="{{ route('orders.show', $order->order_number) }}"
                           class="bg-gray-100 hover:bg-gray-200 text-text font-bold text-xs py-3.5 px-6 rounded-sm text-center transition flex items-center justify-center uppercase tracking-wider">
                            Bayar Nanti
                        </a>
                    </div>
                @else
                    {{-- Instruksi Manual (QRIS / Transfer / Kasir) --}}
                    <div class="bg-gray-50 border-2 border-primary/20 rounded-sm p-6 space-y-5 text-xs text-text">
                        <div class="flex justify-between items-center border-b border-border pb-3">
                            <h3 class="font-black text-primary uppercase text-sm tracking-wide">
                                Transaksi {{ $order->payment_method }}
                            </h3>
                            <span class="bg-primary/10 text-primary font-bold text-[10px] px-2.5 py-1 rounded-sm uppercase">
                                Instruksi Transfer / Kasir
                            </span>
                        </div>

                        @if($order->payment_method === 'QRIS')
                            <div class="text-center space-y-3">
                                <p class="text-text font-semibold">Scan Kode QRIS di bawah ini menggunakan aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay) atau Mobile Banking Anda:</p>
                                <div class="bg-white p-4 inline-block border border-border shadow-sm rounded-sm">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode('RECORD-QRIS|ORDER:' . $order->order_number . '|TOTAL:' . $order->grand_total) }}"
                                         alt="QRIS Code" class="w-48 h-48 mx-auto">
                                </div>
                                <p class="text-xs font-mono font-bold text-text-light">Kode Referensi: RECORD-QRIS-{{ $order->order_number }}</p>
                            </div>
                        @elseif(in_array($order->payment_method, ['BCA', 'BNI', 'BRI', 'Mandiri']))
                            <div class="space-y-4">
                                <p class="text-text font-semibold">Lakukan transfer bank ke nomor Virtual Account di bawah sebelum 24 jam:</p>
                                <div class="bg-white border-2 border-primary p-4 rounded-sm flex items-center justify-between shadow-sm">
                                    <div>
                                        <span class="text-[10px] text-text-light font-bold uppercase block">Nomor Virtual Account {{ $order->payment_method }}</span>
                                        <span class="text-xl font-black font-mono text-primary tracking-wider select-all">
                                            88012{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <button type="button" onclick="navigator.clipboard.writeText('88012{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}'); alert('Nomor Virtual Account berhasil disalin!');"
                                            class="bg-primary hover:bg-primary-light text-white font-bold text-xs px-3.5 py-2 rounded-sm transition uppercase">
                                        <i class="fa-regular fa-copy mr-1"></i> Salin VA
                                    </button>
                                </div>
                                <p class="text-xs text-text-light">Atas Nama Rekening: <strong class="text-text">RECORD OFFICIAL STORE</strong></p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <p class="text-text font-semibold">Tunjukkan kode pembayaran berikut kepada kasir {{ $order->payment_method }} terdekat:</p>
                                <div class="bg-white border-2 border-primary p-4 rounded-sm text-center shadow-sm">
                                    <span class="text-[10px] text-text-light font-bold uppercase block mb-1">Kode Pembayaran Outlet {{ $order->payment_method }}</span>
                                    <span class="text-2xl font-black font-mono text-primary tracking-widest select-all">
                                        REC-{{ strtoupper($order->payment_method) }}-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Konfirmasi — disabled jika belum bayar, aktif otomatis setelah webhook masuk --}}
                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        {{-- Tombol aktif setelah pembayaran dikonfirmasi --}}
                        <a x-show="paymentSuccess"
                           href="{{ route('checkout.payment.finish', $order->order_number) }}"
                           class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-3.5 rounded-sm text-center transition uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm pulse-green">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Pembayaran Dikonfirmasi ✓</span>
                        </a>

                        {{-- Tombol disabled saat belum bayar --}}
                        <button x-show="!paymentSuccess"
                                type="button"
                                disabled
                                title="Tombol aktif setelah pembayaran dikonfirmasi oleh sistem"
                                class="flex-1 bg-gray-300 text-gray-500 font-bold text-xs py-3.5 rounded-sm text-center uppercase tracking-wider flex items-center justify-center gap-2 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                            <span>Menunggu Konfirmasi Pembayaran...</span>
                        </button>

                        <a href="{{ route('orders.show', $order->order_number) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold text-xs py-3.5 px-6 rounded-sm text-center transition flex items-center justify-center uppercase tracking-wider">
                            Lihat Detail Pesanan
                        </a>
                    </div>
                @endif
            @endif

        </div>
    </div>

    <script>
    function paymentPage() {
        return {
            paying:         false,
            paymentSuccess: {{ $order->payment_status === 'paid' ? 'true' : 'false' }},
            pollInterval:   null,

            init() {
                @if($order->payment_method !== 'COD' && $snapToken)
                    setTimeout(() => this.payWithMidtrans(), 800);
                @endif

                // Jika belum lunas, polling status setiap 3 detik
                if (!this.paymentSuccess) {
                    this.startPolling();
                }
            },

            startPolling() {
                this.pollInterval = setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('checkout.payment.status', $order->order_number) }}', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();

                        if (data.payment_status === 'paid') {
                            this.paymentSuccess = true;
                            clearInterval(this.pollInterval);

                            // Delay sedikit agar animasi sukses tampil sempurna
                            setTimeout(() => {
                                window.location.href = '{{ route('checkout.payment.finish', $order->order_number) }}';
                            }, 3000);
                        }
                    } catch (e) {
                        // Abaikan error jaringan sementara
                    }
                }, 3000);
            },

            payWithMidtrans() {
                if (typeof snap === 'undefined') {
                    const existingScript = document.querySelector('script[src*="snap.js"]');
                    if (!existingScript) {
                        const script = document.createElement('script');
                        script.src = '{{ $isProduction ? "https://app.midtrans.com/snap/snap.js" : "https://app.sandbox.midtrans.com/snap/snap.js" }}';
                        script.setAttribute('data-client-key', '{{ $clientKey }}');
                        document.head.appendChild(script);
                    }
                    this.paying = true;
                    setTimeout(() => {
                        if (typeof snap !== 'undefined') {
                            this.paying = false;
                            this.payWithMidtrans();
                        } else {
                            this.paying = false;
                            alert('Sistem pembayaran sedang memuat, silakan klik tombol Bayar Sekarang kembali.');
                        }
                    }, 1000);
                    return;
                }
                this.paying = true;
                snap.pay('{{ $snapToken ?? '' }}', {
                    onSuccess: (result) => {
                        this.paymentSuccess = true;
                        clearInterval(this.pollInterval);
                        setTimeout(() => {
                            window.location.href = '{{ route('checkout.payment.finish', $order->order_number) }}';
                        }, 2500);
                    },
                    onPending: (result) => {
                        this.paying = false;
                    },
                    onError: (result) => {
                        this.paying = false;
                        alert('Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
                    },
                    onClose: () => {
                        this.paying = false;
                    }
                });
            }
        }
    }
    </script>
</x-app-layout>
