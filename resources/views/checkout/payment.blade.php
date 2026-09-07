<x-app-layout>
    <x-slot name="title">Pembayaran Pesanan #{{ $order->order_number }}</x-slot>

    {{-- Midtrans Snap JS --}}
    @if($order->payment_method !== 'COD' && $order->payment_method !== 'MANUAL_BCA' && $snapToken)
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

        {{-- ── Overlay Animasi Sukses ── --}}
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
                    ✓ Pesanan <strong>#{{ $order->order_number }}</strong> sudah LUNAS
                </div>
                <a href="{{ route('orders.show', $order->order_number) }}"
                   class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm py-3.5 rounded-xl transition uppercase tracking-wider">
                    Lihat Detail Pesanan →
                </a>
            </div>
        </div>

        {{-- ── Banner Countdown Batas Waktu Pembayaran ── --}}
        @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-sm p-4 sm:p-5 flex flex-col items-center sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3">
                    <div class="w-10 h-10 rounded-sm bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base shrink-0">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-amber-950">Selesaikan Pembayaran Sebelum</h4>
                        <p class="text-xs text-amber-800 mt-0.5">
                            {{ $expiresAt->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i') }} WIB
                        </p>
                    </div>
                </div>
                <div class="bg-white border border-amber-300/80 px-5 py-2.5 rounded-sm text-center shadow-xs shrink-0 w-full sm:w-auto">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-800 block">Sisa Waktu Pembayaran</span>
                    <span class="font-mono font-black text-xl sm:text-lg text-rose-600 tracking-wider block" x-text="countdownText">
                        Memuat...
                    </span>
                </div>
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden space-y-6 p-6 sm:p-8">

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


                @if($order->payment_method === 'MANUAL_BCA')
                    <div x-show="!paymentSuccess" class="flex items-center justify-center gap-2 text-xs text-blue-200 pt-1">
                        <i class="fa-solid fa-university text-blue-300"></i>
                        <span>{{ $order->payment_proof ? 'Bukti diunggah — menunggu verifikasi admin...' : 'Transfer ke BCA &amp; unggah bukti di bawah ini' }}</span>
                    </div>
                @elseif($order->payment_method !== 'COD')
                    <div x-show="!paymentSuccess" class="flex items-center justify-center gap-2 text-xs text-blue-200 pt-1">
                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <span>Menunggu konfirmasi pembayaran...</span>
                    </div>
                @endif
            </div>

            {{-- Ringkasan Pesanan & Metode Pembayaran --}}
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 space-y-3 text-xs text-gray-800">
                <div class="flex justify-between items-center border-b border-gray-200 pb-2.5">
                    <span class="text-gray-500 font-semibold">Metode Pembayaran</span>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-primary uppercase text-sm">{{ $order->payment_method }}</span>
                        @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                            <button type="button" @click="openChangeModal()"
                                    style="background-color: #1B3A6B; color: #ffffff;"
                                    class="hover:bg-primary-light text-[10px] font-bold px-2.5 py-1 rounded-sm uppercase tracking-wider transition shadow-xs flex items-center gap-1">
                                <i class="fa-solid fa-arrows-rotate text-[9px]"></i> Ubah
                            </button>
                        @endif
                    </div>
                </div>
                <div class="flex justify-between items-center border-b border-gray-200 pb-2.5">
                    <span class="text-gray-500 font-semibold">Kurir Pengiriman</span>
                    <span class="font-bold text-gray-900">{{ $order->courier }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 font-semibold">Penerima Paket</span>
                    <span class="font-bold text-gray-900 text-right max-w-xs truncate">
                        {{ $order->shipping_address['recipient_name'] ?? '' }} ({{ $order->shipping_address['city'] ?? '' }})
                    </span>
                </div>
            </div>

            {{-- Pesan Error Midtrans jika ada --}}
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

            @if($order->payment_method === 'MANUAL_BCA')
                {{-- ═══════════════ INSTRUKSI TRANSFER MANUAL BCA & UPLOAD BUKTI ═══════════════ --}}
                <div class="space-y-6">

                    {{-- ⚠️ PERINGATAN HATI-HATI PENIPUAN & SALAH TRANSFER ⚠️ --}}
                    <div class="rounded-2xl border-2 border-rose-400 bg-rose-50/90 p-5 sm:p-6 text-slate-800 shadow-sm space-y-3.5">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 shadow-xs" style="background-color: #E11D48; color: #ffffff;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-rose-900 text-sm sm:text-base uppercase tracking-wide flex items-center gap-2">
                                    <span>Penting: Hati-Hati Penipuan &amp; Awas Keliru Transfer!</span>
                                </h3>
                                <p class="text-xs text-rose-700 font-semibold mt-0.5">
                                    Mohon periksa nomor rekening dan nama penerima dengan sangat teliti sebelum memproses transfer:
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-rose-200 p-4 shadow-2xs space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rose-100 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-1 rounded-md font-black text-xs text-white shadow-xs" style="background-color: #00529B; color: #ffffff;">BANK BCA</span>
                                    <span class="text-xs text-slate-600 font-medium">Rekening Resmi Toko:</span>
                                    <strong class="text-base sm:text-lg font-black font-mono text-slate-900 tracking-wider">1000028122</strong>
                                </div>
                                <div class="text-xs text-slate-700">
                                    Atas Nama: <strong class="text-rose-700 font-black text-sm uppercase">Lily Minawati Prajogo</strong>
                                </div>
                            </div>

                            <div class="text-[11px] sm:text-xs text-slate-700 leading-relaxed space-y-2">
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-circle-xmark text-rose-600 text-sm mt-0.5 shrink-0"></i>
                                    <span><strong>JANGAN TRANSFER KE REKENING LAIN:</strong> Toko kami <strong>HANYA</strong> menggunakan rekening BCA atas nama <strong>Lily Minawati Prajogo</strong> di atas. Kami <strong>TIDAK PERNAH</strong> meminta Anda mentransfer ke nomor rekening lain atau atas nama selain itu.</span>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-shield-halved text-rose-600 text-sm mt-0.5 shrink-0"></i>
                                    <span><strong>PERINGATAN KERAS:</strong> Pastikan nama <strong>Lily Minawati Prajogo</strong> muncul di layar konfirmasi ATM / m-Banking / KlikBCA sebelum Anda memproses transaksi. <strong>Segala kelalaian, salah ketik, atau kesalahan transfer ke pihak lain adalah SEPENUHNYA DI LUAR TANGGUNG JAWAB KAMI</strong>.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 1. Kartu Rekening Tujuan Transfer --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5 sm:p-6 text-slate-800 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-blue-200/70 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm shadow-xs shrink-0" style="background-color: #00529B; color: #ffffff;">
                                    BCA
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-900 text-sm sm:text-base uppercase tracking-wide">
                                        Transfer Bank BCA (Manual)
                                    </h3>
                                    <p class="text-[11px] text-blue-800">Silakan transfer tepat sesuai total tagihan ke rekening di bawah ini:</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-200 w-fit">
                                <i class="fa-solid fa-user-check text-[10px]"></i> Verifikasi Admin
                            </span>
                        </div>

                        {{-- Rincian Rekening --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            {{-- Nomor Rekening --}}
                            <div class="bg-white border border-blue-200 p-4 rounded-xl shadow-xs flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider block">Nomor Rekening BCA</span>
                                    <span class="text-xl sm:text-2xl font-black font-mono tracking-wider select-all" style="color: #00529B;" id="bca-norek">1000028122</span>
                                    <span class="text-xs text-gray-600 font-semibold block mt-0.5">a.n <strong>Lily Minawati Prajogo</strong></span>
                                </div>
                                <button type="button"
                                        onclick="navigator.clipboard.writeText('1000028122'); alert('Nomor Rekening BCA 1000028122 berhasil disalin!');"
                                        class="text-white font-bold text-xs px-3.5 py-2.5 rounded-lg transition uppercase tracking-wider shadow-xs flex items-center gap-1.5 shrink-0 hover:opacity-90 cursor-pointer"
                                        style="background-color: #00529B; color: #ffffff;">
                                    <i class="fa-regular fa-copy"></i>
                                    <span>Salin</span>
                                </button>
                            </div>

                            {{-- Jumlah Transfer --}}
                            <div class="bg-white border border-blue-200 p-4 rounded-xl shadow-xs flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider block">Total Tagihan Transfer</span>
                                    <span class="text-xl sm:text-2xl font-black font-mono text-emerald-600 tracking-wider select-all">
                                        {{ $order->formatted_grand_total }}
                                    </span>
                                    <span class="text-[10px] text-gray-500 block mt-0.5">Transfer persis hingga digit terakhir</span>
                                </div>
                                <button type="button"
                                        onclick="navigator.clipboard.writeText('{{ (int) round($order->grand_total) }}'); alert('Nominal transfer berhasil disalin!');"
                                        class="bg-gray-100 hover:bg-gray-200 text-slate-800 font-bold text-xs px-3.5 py-2.5 rounded-lg transition uppercase tracking-wider border border-gray-200 shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i class="fa-regular fa-copy"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>

                        {{-- Notice --}}
                        <div class="bg-blue-100/60 border border-blue-200/80 rounded-lg p-3 text-xs text-blue-900 flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 shrink-0"></i>
                            <div class="leading-relaxed text-[11px]">
                                <strong>PENTING:</strong> Setelah menyelesaikan transfer di ATM / m-Banking / KlikBCA, mohon <strong>unggah foto bukti/struk transfer</strong> pada form di bawah ini. Admin toko akan mengecek mutasi rekening sebelum pesanan Anda diproses dan dikirim.
                            </div>
                        </div>
                    </div>

                    {{-- 2. Notifikasi Penolakan (Jika ada catatan penolakan dari admin) --}}
                    @if($order->payment_rejection_note)
                        <div class="bg-rose-50 border-2 border-rose-300 rounded-xl p-4 sm:p-5 text-xs text-rose-900 space-y-2 shadow-sm">
                            <div class="flex items-center gap-2 font-black text-rose-800 text-sm">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                                <span>Bukti Pembayaran Ditolak Admin</span>
                            </div>
                            <p class="text-rose-700 leading-relaxed font-medium">
                                Catatan Admin: <strong>{{ $order->payment_rejection_note }}</strong>
                            </p>
                            <p class="text-[11px] text-rose-600">
                                Silakan pastikan dana sudah masuk ke rekening BCA toko dan unggah kembali foto bukti transfer yang jelas dan valid.
                            </p>
                        </div>
                    @endif

                    {{-- 3. Area Bukti Transfer (Status & Form Upload) --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5 sm:p-6 space-y-5" x-data="{ showReupload: false, previewSrc: null, fileName: '', fileSize: '' }">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h4 class="font-black text-slate-900 text-sm uppercase tracking-wide flex items-center gap-2">
                                <i class="fa-solid fa-receipt text-blue-600"></i>
                                <span>Bukti Transfer Pembayaran</span>
                            </h4>
                            @if($order->payment_proof)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ $order->payment_status === 'paid' ? '✓ Pembayaran Lunas' : '⏳ Menunggu Pengecekan Admin' }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Belum Diunggah
                                </span>
                            @endif
                        </div>

                        {{-- Tampilan Bukti yang Sudah Diunggah --}}
                        @if($order->payment_proof)
                            <div class="space-y-4" x-show="!showReupload">
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row items-center gap-4">
                                    <a href="{{ $order->payment_proof_url }}" target="_blank" class="block shrink-0 group relative overflow-hidden rounded-lg border border-gray-300 shadow-xs">
                                        <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer" class="w-32 h-32 object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i> Lihat
                                        </div>
                                    </a>
                                    <div class="space-y-1.5 text-xs text-slate-700 flex-1 text-center sm:text-left">
                                        <p class="font-bold text-slate-900">
                                            Bukti transfer telah berhasil diunggah
                                        </p>
                                        <p class="text-[11px] text-gray-500">
                                            Diunggah pada: <span class="font-semibold text-slate-700">{{ $order->payment_proof_uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</span>
                                        </p>
                                        <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-[11px] leading-relaxed">
                                            <i class="fa-solid fa-hourglass-half mr-1 text-amber-600"></i>
                                            Admin sedang mencocokkan bukti ini dengan mutasi rekening BCA toko. Pesanan akan segera diproses begitu dana terkonfirmasi.
                                        </div>
                                        @if($order->payment_status !== 'paid' && $order->status !== 'cancelled')
                                            <button type="button" @click="showReupload = true" class="text-blue-600 hover:text-blue-800 text-xs font-bold underline inline-flex items-center gap-1 pt-1 cursor-pointer">
                                                <i class="fa-solid fa-arrows-rotate"></i> Ganti / Unggah Ulang Bukti
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Form Unggah Bukti Baru / Re-upload --}}
                        <div x-show="!{{ $order->payment_proof ? 'true' : 'false' }} || showReupload" class="space-y-4">
                            @if($order->payment_status !== 'paid' && $order->status !== 'cancelled')
                                <form action="{{ route('checkout.payment.upload-proof', $order->order_number) }}"
                                      method="POST"
                                      enctype="multipart/form-data"
                                      class="space-y-4"
                                      @submit="if(!document.getElementById('proof_input').files.length){ alert('Pilih foto bukti transfer terlebih dahulu!'); $event.preventDefault(); }">
                                    @csrf

                                    <div>
                                        <label class="block text-xs font-bold text-slate-800 mb-1.5 uppercase tracking-wide">
                                            PILIH FOTO STRUK / SCREENSHOT BUKTI TRANSFER
                                        </label>
                                        <div class="border-2 border-dashed border-blue-300 hover:border-blue-600 rounded-2xl p-6 text-center transition bg-blue-50/20 cursor-pointer relative"
                                             @click="document.getElementById('proof_input').click()">
                                            <input type="file"
                                                   id="proof_input"
                                                   name="payment_proof"
                                                   accept="image/png, image/jpeg, image/jpg, image/webp"
                                                   class="hidden"
                                                   @change="
                                                       const file = $event.target.files[0];
                                                       if (file) {
                                                           fileName = file.name;
                                                           fileSize = (file.size / 1024 < 1024) ? Math.round(file.size / 1024) + ' KB' : (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                                           const reader = new FileReader();
                                                           reader.onload = (e) => { previewSrc = e.target.result; };
                                                           reader.readAsDataURL(file);
                                                       }
                                                   ">
                                            
                                            <template x-if="!previewSrc">
                                                <div class="space-y-3 py-2">
                                                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto text-2xl shadow-xs" style="background-color: #EFF6FF; color: #00529B;">
                                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-900">Klik di sini untuk memilih foto bukti pembayaran</p>
                                                        <p class="text-xs text-gray-500 mt-0.5">Mendukung format JPG, JPEG, PNG, WEBP (Maks. 5 MB)</p>
                                                    </div>
                                                    <div>
                                                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm hover:opacity-90 transition cursor-pointer" style="background-color: #00529B; color: #ffffff;">
                                                            <i class="fa-regular fa-image"></i>
                                                            <span>Pilih Berkas Foto</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="previewSrc">
                                                <div class="space-y-3 py-1">
                                                    <img :src="previewSrc" alt="Pratinjau Bukti" class="max-h-56 mx-auto rounded-xl shadow-md border-2 border-blue-300 object-contain bg-white">
                                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-bold shadow-2xs">
                                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                        <span x-text="fileName + ' (' + fileSize + ')'"></span>
                                                    </div>
                                                    <p class="text-xs text-blue-700 font-bold hover:underline block">Foto siap diunggah. Klik di sini jika ingin mengganti foto lain.</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- TOMBOL SIMPAN & UNGGAH BUKTI PEMBAYARAN (SANGAT JELAS & MENCOLOK) --}}
                                    <div class="pt-2 space-y-2">
                                        <div class="flex flex-col sm:flex-row items-center gap-3">
                                            <button type="submit"
                                                    id="btn_simpan_bukti"
                                                    class="w-full flex-1 text-white font-black text-sm py-4 px-6 rounded-xl transition uppercase tracking-wider shadow-md flex items-center justify-center gap-2.5 hover:opacity-95 active:scale-[0.99] cursor-pointer"
                                                    style="background-color: #00529B; color: #ffffff;">
                                                <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                                                <span>Unggah &amp; Simpan Bukti Pembayaran</span>
                                            </button>
                                            @if($order->payment_proof)
                                                <button type="button" @click="showReupload = false; previewSrc = null"
                                                        class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs py-4 px-5 rounded-xl transition uppercase tracking-wider cursor-pointer">
                                                    Batal
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-500 text-center flex items-center justify-center gap-1.5 pt-1">
                                            <i class="fa-solid fa-shield-check text-slate-400"></i>
                                            <span>Pastikan Anda menekan tombol di atas agar bukti tersimpan dan dapat diverifikasi oleh admin toko.</span>
                                        </p>
                                    </div>
                                </form>
                            @else
                                <p class="text-xs text-gray-500 italic">Pesanan ini sudah selesai atau dibatalkan.</p>
                            @endif
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-3 border-t border-gray-100">
                            <a href="{{ route('orders.show', $order->order_number) }}"
                               class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs py-3.5 px-6 rounded-xl text-center transition flex items-center justify-center uppercase tracking-wider gap-2">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Lihat Detail Pesanan</span>
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($order->payment_method !== 'COD')
                {{-- Konten Midtrans Snap --}}
                @if($snapToken)
                    <div class="bg-sky-50 border border-sky-200 text-sky-900 rounded-sm p-5 text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-sky-800 text-sm">
                            <i class="fa-solid fa-shield-halved text-sky-600"></i>
                            <span>Midtrans Payment Gateway</span>
                        </div>
                        <p class="leading-relaxed text-sky-800">
                            Jendela pembayaran otomatis akan terbuka. Selesaikan pembayaran sebelum waktu kedaluwarsa, dan status akan otomatis terkonfirmasi.
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
                           class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs py-3.5 px-6 rounded-sm text-center transition flex items-center justify-center uppercase tracking-wider">
                            Bayar Nanti
                        </a>
                    </div>
                @else
                    {{-- Instruksi Manual Fallback --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-sm p-6 space-y-5 text-xs text-gray-800">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                            <h3 class="font-black text-primary uppercase text-sm tracking-wide">
                                Transaksi {{ $order->payment_method }}
                            </h3>
                            <span class="bg-primary/10 text-primary font-bold text-[10px] px-2.5 py-1 rounded-sm uppercase">
                                Instruksi Transfer / Kasir
                            </span>
                        </div>

                        @if($order->payment_method === 'QRIS')
                            <div class="text-center space-y-3">
                                <p class="text-gray-700 font-semibold">Scan Kode QRIS di bawah ini menggunakan aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay) atau Mobile Banking Anda:</p>
                                <div class="bg-white p-4 inline-block border border-gray-200 shadow-sm rounded-sm">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode('RECORD-QRIS|ORDER:' . $order->order_number . '|TOTAL:' . $order->grand_total) }}"
                                         alt="QRIS Code" class="w-48 h-48 mx-auto">
                                </div>
                                <p class="text-xs font-mono font-bold text-gray-500">Kode Referensi: RECORD-QRIS-{{ $order->order_number }}</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <p class="text-gray-700 font-semibold">Lakukan transfer bank ke nomor Virtual Account di bawah sebelum batas waktu pembayaran:</p>
                                <div class="bg-white border-2 border-primary p-4 rounded-sm flex items-center justify-between shadow-sm">
                                    <div>
                                        <span class="text-[10px] text-gray-500 font-bold uppercase block">Nomor Virtual Account {{ $order->payment_method }}</span>
                                        <span class="text-xl font-black font-mono text-primary tracking-wider select-all">
                                            88012{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <button type="button" onclick="navigator.clipboard.writeText('88012{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}'); alert('Nomor Virtual Account berhasil disalin!');"
                                            class="bg-primary hover:bg-primary-light text-white font-bold text-xs px-3.5 py-2 rounded-sm transition uppercase">
                                        <i class="fa-regular fa-copy mr-1"></i> Salin VA
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('orders.show', $order->order_number) }}"
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold text-xs py-3.5 px-6 rounded-sm text-center transition flex items-center justify-center uppercase tracking-wider">
                            Lihat Detail Pesanan
                        </a>
                    </div>
                @endif
            @endif

        </div>

        {{-- ── MODAL UBAH METODE PEMBAYARAN ── --}}
        <div x-show="showChangePaymentModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="showChangePaymentModal = false"
                 class="bg-white rounded-sm shadow-2xl max-w-lg w-full p-6 sm:p-7 text-left space-y-5 border border-gray-200 max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-sm bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-primary uppercase tracking-wide">Ubah Metode Pembayaran</h3>
                            <p class="text-[11px] text-gray-500">Pilih saluran pembayaran baru untuk pesanan #{{ $order->order_number }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showChangePaymentModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg leading-none">&times;</button>
                </div>

                {{-- Alert Error --}}
                <div x-show="changeError" x-cloak class="p-3 rounded-sm bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span x-text="changeError"></span>
                </div>

                {{-- List Pilihan Metode Pembayaran --}}
                @php
                    $pembayaranTersedia = [
                        ['code' => 'MANUAL_BCA', 'name' => 'Transfer Bank Manual BCA',          'desc' => 'BCA 1000028122 a.n Lily Minawati Prajogo', 'icon' => 'fa-solid fa-money-bill-transfer'],
                        ['code' => 'QRIS',      'name' => 'QRIS (Semua E-Wallet & M-Banking)', 'desc' => 'GoPay, OVO, Dana, ShopeePay, LinkAja', 'icon' => 'fa-solid fa-qrcode'],
                        ['code' => 'BCA',       'name' => 'Transfer BCA Virtual Account',       'desc' => 'Verifikasi Otomatis 24 Jam',           'icon' => 'fa-solid fa-building-columns'],
                        ['code' => 'BNI',       'name' => 'Transfer BNI Virtual Account',       'desc' => 'Verifikasi Otomatis 24 Jam',           'icon' => 'fa-solid fa-building-columns'],
                        ['code' => 'BRI',       'name' => 'Transfer BRI Virtual Account',       'desc' => 'Verifikasi Otomatis 24 Jam',           'icon' => 'fa-solid fa-building-columns'],
                        ['code' => 'Mandiri',   'name' => 'Mandiri Bill Payment',               'desc' => 'Verifikasi Otomatis 24 Jam',           'icon' => 'fa-solid fa-building-columns'],
                        ['code' => 'Indomaret', 'name' => 'Indomaret / Ceriamart',              'desc' => 'Bayar di Kasir Outlet Terdekat',       'icon' => 'fa-solid fa-store'],
                        ['code' => 'Alfamart',  'name' => 'Alfamart / Alfamidi',                'desc' => 'Bayar di Kasir Outlet Terdekat',       'icon' => 'fa-solid fa-store'],
                    ];
                @endphp

                <div class="space-y-2">
                    @foreach($pembayaranTersedia as $item)
                        @php
                            $isMaintenance = ($item['code'] !== 'MANUAL_BCA');
                        @endphp
                        <label @if(!$isMaintenance) @click="selectedNewMethod = '{{ $item['code'] }}'" @endif
                               class="border p-3.5 rounded-sm transition flex items-center justify-between {{ $isMaintenance ? 'bg-gray-50/70 border-gray-200 opacity-60 cursor-not-allowed' : 'cursor-pointer' }}"
                               @if(!$isMaintenance)
                                   :class="selectedNewMethod === '{{ $item['code'] }}' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 hover:border-gray-300 bg-white'"
                               @endif>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-sm bg-gray-50 flex items-center justify-center border border-gray-200 shrink-0 text-primary">
                                    <i class="{{ $item['icon'] }} text-xs"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-bold text-gray-800">{{ $item['name'] }}</p>
                                        @if($isMaintenance)
                                            <span class="bg-amber-100 text-amber-800 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider border border-amber-300">Maintenance</span>
                                        @else
                                            <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider border border-emerald-300">Tersedia</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] {{ $isMaintenance ? 'text-amber-700 font-medium' : 'text-gray-500' }}">
                                        {{ $isMaintenance ? 'Sementara dinonaktifkan (Sedang Maintenance)' : $item['desc'] }}
                                    </p>
                                </div>
                            </div>
                            @if($isMaintenance)
                                <i class="fa-solid fa-lock text-gray-400 text-xs shrink-0"></i>
                            @else
                                <input type="radio" name="modal_payment_method" value="{{ $item['code'] }}" x-model="selectedNewMethod" class="text-primary focus:ring-primary h-4 w-4">
                            @endif
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end items-center gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showChangePaymentModal = false"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2.5 rounded-sm transition uppercase">
                        Batal
                    </button>
                    <button type="button"
                            @click="submitChangePaymentMethod()"
                            :disabled="changingMethod || selectedNewMethod === '{{ $order->payment_method }}'"
                            style="background-color: #1B3A6B; color: #ffffff;"
                            class="hover:bg-primary-light text-xs font-bold px-5 py-2.5 rounded-sm uppercase tracking-wider transition shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!changingMethod"><i class="fa-solid fa-check"></i> Simpan & Lanjutkan</span>
                        <span x-show="changingMethod" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Mengubah...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
    function paymentPage() {
        return {
            paying: false,
            paymentSuccess: {{ $order->payment_status === 'paid' ? 'true' : 'false' }},
            pollInterval: null,
            showChangePaymentModal: false,
            selectedNewMethod: '{{ $order->payment_method }}',
            changingMethod: false,
            changeError: '',
            secondsRemaining: {{ $secondsRemaining ?? 86400 }},
            countdownText: '',
            countdownInterval: null,

            init() {
                this.updateCountdown();
                this.countdownInterval = setInterval(() => {
                    if (this.secondsRemaining > 0) {
                        this.secondsRemaining--;
                        this.updateCountdown();
                    } else {
                        clearInterval(this.countdownInterval);
                        this.countdownText = 'Waktu Habis';
                        window.location.reload();
                    }
                }, 1000);

                @if($order->payment_method !== 'COD' && $order->payment_method !== 'MANUAL_BCA' && $snapToken)
                    setTimeout(() => this.payWithMidtrans(), 800);
                @endif

                if (!this.paymentSuccess) {
                    this.startPolling();
                }
            },

            updateCountdown() {
                if (this.secondsRemaining <= 0) {
                    this.countdownText = '00:00:00 (Waktu Habis)';
                    return;
                }
                const h = Math.floor(this.secondsRemaining / 3600);
                const m = Math.floor((this.secondsRemaining % 3600) / 60);
                const s = this.secondsRemaining % 60;
                const pad = (num) => String(num).padStart(2, '0');
                this.countdownText = `${pad(h)} jam ${pad(m)} mnt ${pad(s)} dtk`;
            },

            openChangeModal() {
                this.showChangePaymentModal = true;
                this.changeError = '';
            },

            async submitChangePaymentMethod() {
                this.changingMethod = true;
                this.changeError = '';
                try {
                    const res = await fetch('{{ route('checkout.payment.change-method', $order->order_number) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ payment_method: this.selectedNewMethod })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        window.location.href = data.redirect || window.location.href;
                    } else {
                        this.changeError = data.message || 'Gagal mengubah metode pembayaran.';
                        this.changingMethod = false;
                    }
                } catch (e) {
                    this.changeError = 'Terjadi kendala jaringan. Silakan coba lagi.';
                    this.changingMethod = false;
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

                            setTimeout(() => {
                                window.location.href = '{{ route('checkout.payment.finish', $order->order_number) }}';
                            }, 3000);
                        }
                    } catch (e) {
                        // Abaikan error jaringan polling sementara
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
        };
    }
    </script>
</x-app-layout>