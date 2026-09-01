<x-app-layout>
    <x-slot name="title">Riwayat Pesanan Saya</x-slot>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 text-gray-900"
         x-data="{ activeTab: 'all' }">

        <!-- Title -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-primary uppercase tracking-wide">Riwayat Pesanan</h1>
                <p class="text-xs text-gray-600 mt-1">Kelola dan lacak status pesanan produk Anda di Record</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-gray-700 hover:text-primary uppercase flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i> Akun Saya
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white border border-gray-200 p-12 text-center rounded-sm shadow-sm space-y-4">
                <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-500 border border-gray-200">
                    <i class="fa-solid fa-bag-shopping text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Belum Ada Pesanan</h3>
                <p class="text-xs text-gray-600 max-w-sm mx-auto">Anda belum melakukan transaksi pembelian di Record.</p>
                <div class="pt-2">
                    <a href="{{ route('products.index') }}"
                       class="inline-block bg-primary hover:bg-primary-light text-white text-xs font-bold px-8 py-3 rounded-sm transition uppercase tracking-wider shadow-sm">
                        Belanja Sekarang
                    </a>
                </div>
            </div>
        @else
            <!-- Filter Tabs Gunakan Theme Primary Navy (#1B3A6B) 100% Terjamin Kontras -->
            <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto">
                <button @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold'"
                        class="px-4 py-2 rounded-sm text-xs transition shrink-0 uppercase tracking-wider">
                    Semua ({{ $orders->total() }})
                </button>
                <button @click="activeTab = 'pending'"
                        :class="activeTab === 'pending' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold'"
                        class="px-4 py-2 rounded-sm text-xs transition shrink-0 uppercase tracking-wider">
                    Menunggu Bayar
                </button>
                <button @click="activeTab = 'processing'"
                        :class="activeTab === 'processing' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold'"
                        class="px-4 py-2 rounded-sm text-xs transition shrink-0 uppercase tracking-wider">
                    Diproses
                </button>
                <button @click="activeTab = 'shipped'"
                        :class="activeTab === 'shipped' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold'"
                        class="px-4 py-2 rounded-sm text-xs transition shrink-0 uppercase tracking-wider">
                    Dikirim
                </button>
                <button @click="activeTab = 'completed'"
                        :class="activeTab === 'completed' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold'"
                        class="px-4 py-2 rounded-sm text-xs transition shrink-0 uppercase tracking-wider">
                    Selesai
                </button>
            </div>

            <!-- List Cards Pesanan -->
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div x-show="activeTab === 'all' || activeTab === '{{ $order->status }}'"
                         class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm hover:border-primary transition space-y-4">
                        
                        {{-- Card Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-primary text-sm">#{{ $order->order_number }}</span>
                                <span class="text-xs text-gray-500">• {{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- Tanda yang menetap sampai semua barangnya dinilai. --}}
                                @php
                                    $belumNilai = $order->status === 'completed'
                                        ? $order->items->filter(fn ($i) => $i->review === null)->count()
                                        : 0;
                                @endphp
                                @if($belumNilai > 0)
                                    <a href="{{ route('orders.show', $order->order_number) }}#nilai"
                                       class="belum-nilai" title="Beri penilaian untuk produk ini">
                                        <i class="fa-solid fa-star"></i>
                                        <span>Belum Dinilai</span>
                                    </a>
                                @endif

                                <span class="px-3 py-1 rounded-sm text-[10px] font-bold uppercase tracking-wider
                                    @if($order->status === 'pending') bg-amber-100 text-amber-900 border border-amber-300
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-900 border border-blue-300
                                    @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-900 border border-indigo-300
                                    @elseif($order->status === 'completed') bg-emerald-100 text-emerald-900 border border-emerald-300
                                    @else bg-red-100 text-red-900 border border-red-300
                                    @endif">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Item Preview --}}
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                @php $firstItem = $order->items->first(); @endphp
                                <div class="h-16 w-16 bg-gray-50 border border-gray-200 rounded-sm flex-shrink-0 flex items-center justify-center p-1">
                                    <img src="{{ $firstItem?->product?->image_url ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80' }}"
                                         alt="{{ $firstItem?->product_name }}"
                                         class="object-contain max-h-full max-w-full"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80';">
                                </div>
                                <div class="min-w-0 text-xs">
                                    <h4 class="font-bold text-gray-900 truncate text-sm">{{ $firstItem?->product_name ?? 'Produk' }}</h4>
                                    @if($order->items->count() > 1)
                                        <p class="text-[11px] text-gray-600 font-semibold mt-0.5">+ {{ $order->items->count() - 1 }} produk lainnya</p>
                                    @else
                                        <p class="text-[11px] text-gray-600 mt-0.5">Jumlah: {{ $firstItem?->quantity }} pasang</p>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-[10px] text-gray-500 uppercase font-bold block">Total Belanja</span>
                                <span class="text-sm font-black text-accent">{{ $order->formatted_grand_total }}</span>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-gray-100 pt-4">
                            <div class="text-xs text-gray-600 flex items-center gap-2">
                                <i class="fa-solid fa-truck text-primary"></i>
                                <span>Kurir: <strong class="text-gray-900">{{ $order->courier }}</strong></span>
                                @if($order->tracking_number)
                                    <span class="bg-gray-100 text-gray-800 font-mono text-[10px] px-2 py-0.5 rounded-sm font-bold border border-gray-300">
                                        Resi: {{ $order->tracking_number }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                                    <a href="{{ route('checkout.payment', $order->order_number) }}"
                                       class="bg-accent hover:bg-accent-dark text-white text-xs font-bold px-4 py-2 rounded-sm transition uppercase tracking-wider shadow-sm">
                                        Bayar Sekarang
                                    </a>
                                @elseif($order->payment_status === 'pending_verification')
                                    <span class="bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold px-3 py-1.5 rounded-sm uppercase tracking-wider">
                                        <i class="fa-solid fa-clock mr-1"></i> Menunggu Konfirmasi Admin
                                    </span>
                                @endif

                                @if($belumNilai > 0)
                                    <a href="{{ route('orders.show', $order->order_number) }}#nilai"
                                       class="nilai-ajak">
                                        <i class="fa-solid fa-star"></i>
                                        Beri Penilaian
                                    </a>
                                @endif
                                <a href="{{ route('orders.show', $order->order_number) }}"
                                   class="bg-primary hover:bg-primary-light text-white text-xs font-bold px-5 py-2 rounded-sm transition uppercase tracking-wider">
                                    Detail & Tracking
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="pt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
@push('styles')
<style>
    /* ── Tanda "Belum Dinilai" ── Ditulis tangan seperti blok lain, sebab hasil build Tailwind yang ter... */

    .belum-nilai {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px;
        border: 1px solid #FCD34D; border-radius: 3px;
        background: #FFFBEB;
        color: #92400E;
        font-size: 10px; font-weight: 800;
        letter-spacing: .06em; text-transform: uppercase;
        text-decoration: none;
        transition: background-color 160ms ease, border-color 160ms ease;
    }
    .belum-nilai:hover { background: #FEF3C7; border-color: #F59E0B; }

    /* Bintangnya berdenyut pelan — cukup untuk menarik pandangan sekali, tidak cukup untuk mengganggu o... */
    .belum-nilai i {
        font-size: 9px; color: #F59E0B;
        animation: belum-nilai-denyut 2.8s ease-in-out infinite;
    }

    @keyframes belum-nilai-denyut {
        0%, 70%, 100% { transform: scale(1);    opacity: 1;   }
        80%           { transform: scale(1.28); opacity: .75; }
    }

    .nilai-ajak {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        border-radius: 3px;
        background: #F59E0B;
        color: #fff;
        font-size: 12px; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
        text-decoration: none;
        box-shadow: 0 1px 3px rgb(0 0 0 / .1);
        transition: background-color 160ms ease;
    }
    .nilai-ajak:hover { background: #D97706; }
    .nilai-ajak i { font-size: 11px; }

    @media (prefers-reduced-motion: reduce) {
        .belum-nilai i { animation: none; }
    }
</style>
@endpush

</x-app-layout>
