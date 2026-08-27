@props([
    'nomor' => '6281323065554',
    'pesan' => 'Halo Admin RECORD, saya tertarik dengan produk di website recordshoes.com. Boleh tanya-tanya?',
])

@php
    $urlWa = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $nomor) . '?text=' . urlencode($pesan);
@endphp

{{-- Tombol WhatsApp Melayang (Floating WhatsApp Button) --}}
<div x-data="{ showTooltip: false, pulsed: false }"
     x-init="setTimeout(() => pulsed = true, 2000)"
     class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 z-40 flex items-center group">

    {{-- Balon Teks / Tooltip Ramah --}}
    <div class="hidden sm:flex items-center mr-3 bg-white/95 backdrop-blur-md text-gray-800 text-xs font-semibold px-3.5 py-2 rounded-2xl shadow-xl border border-gray-100 ring-1 ring-black/5 transition-all duration-300 opacity-90 group-hover:opacity-100 group-hover:-translate-x-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
        <span>Chat CS WhatsApp</span>
    </div>

    {{-- Tombol Bulat Hijau WhatsApp --}}
    <a href="{{ $urlWa }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Hubungi Admin RECORD via WhatsApp"
       class="relative w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-[#25D366] hover:bg-[#20ba59] text-white flex items-center justify-center shadow-2xl shadow-emerald-600/40 hover:scale-110 active:scale-95 transition-all duration-300 transform group cursor-pointer focus:outline-none focus:ring-4 focus:ring-emerald-300">

        {{-- Gelombang Efek Ping Halus --}}
        <span class="absolute inset-0 rounded-full bg-[#25D366] opacity-40 animate-ping duration-1000 pointer-events-none"
              style="animation-duration: 3s;"></span>

        {{-- Ikon Resmi WhatsApp FontAwesome --}}
        <i class="fa-brands fa-whatsapp text-2xl sm:text-3xl drop-shadow-sm relative z-10 transition-transform duration-300 group-hover:rotate-12"></i>

        {{-- Dot Status Online --}}
        <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-emerald-300 border-2 border-white rounded-full z-20"></span>
    </a>
</div>