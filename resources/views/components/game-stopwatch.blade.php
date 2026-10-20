{{--
    Record Shoes - Stopwatch Challenge Mini-Game Widget
    Komponen interaktif berhadiah voucher diskon (10.00 detik pas = 50rb, mendekati = 30rb, lainnya = 10rb)
    Support Responsive HP & Desktop, Luxury Ticket Design, Confetti, Alpine.js State
--}}

@php
    $swSetting = \App\Models\GameSetting::forGame('stopwatch');
    $isSwActive = $swSetting ? (bool) $swSetting->is_active : true;
    $swConfig = $swSetting ? ($swSetting->config ?? []) : [];
    $swTiers = $swConfig['tiers'] ?? [];

    // Tier 1: Perfect
    $perfectTier = $swTiers['perfect'] ?? [];
    $perfectAmount = (int) ($perfectTier['amount'] ?? 50000);
    $perfectMin = (float) ($perfectTier['min'] ?? 9.98);
    $perfectMax = (float) ($perfectTier['max'] ?? 10.02);

    // Tier 2: Near
    $nearTier = $swTiers['near'] ?? [];
    $nearAmount = (int) ($nearTier['amount'] ?? 30000);
    $nearMin = (float) ($nearTier['min'] ?? 9.85);
    $nearMax = (float) ($nearTier['max'] ?? 10.15);

    // Tier 3: Good (Partisipasi)
    $goodTier = $swTiers['good'] ?? [];
    $goodAmount = (int) ($goodTier['amount'] ?? 10000);

    // Tertinggi untuk badge tombol floating (otomatis berubah sesuai setting admin, misal Rp 30.000)
    $maxVoucherAmount = max($perfectAmount, $nearAmount, $goodAmount);
    $maxVoucherFormatted = number_format($maxVoucherAmount, 0, ',', '.');

    // Batas kesempatan main harian
    $swDailyLimit = (int) ($swConfig['daily_limit'] ?? 1);
@endphp

@if($isSwActive)
<div x-data="gameStopwatchWidget()" 
     x-init="initWidget()"
     @open-stopwatch-game.window="openModal()"
     class="stopwatch-game-root">

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 1. FLOATING ACTION BUTTON (Pojok Kanan Bawah)                      --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-cloak
         x-show="!modalOpen" 
         class="stopwatch-floating-trigger">
        
        {{-- Floating Pill Button --}}
        <button @click="openModal()" 
                type="button"
                aria-label="Buka Tantangan Stopwatch 10 Detik"
                class="stopwatch-floating-btn group hover:scale-[1.03] active:scale-95 transition-all duration-200">
            
            {{-- Icon Stopwatch Container --}}
            <div class="relative flex items-center justify-center w-10 h-10 rounded-full bg-white text-[#1B3A6B] shadow-sm flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="13" r="8"></circle>
                    <path d="M12 9v4l2.5 2.5"></path>
                    <path d="M10 2h4M12 2v3"></path>
                </svg>
            </div>

            {{-- Text Label --}}
            <div class="flex flex-col text-left">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200">10.00s Challenge</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-white/20 text-white">Voucher</span>
                </div>
                <span class="text-xs sm:text-sm font-bold text-white tracking-tight flex items-center gap-1">
                    Dapatkan s/d Rp {{ $maxVoucherFormatted }}
                    <svg class="w-3.5 h-3.5 text-blue-200 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            </div>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 2. MODAL OVERLAY & CONTAINER                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-cloak
         x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="stopwatch-modal-backdrop">

        {{-- Backdrop click (hanya bisa tutup jika bukan saat holding voucher) --}}
        <div class="fixed inset-0" @click="handleBackdropClick()"></div>

        {{-- Modal Box (Clean White Background, Navy Blue Accents, Anti AI-Slop) --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @keydown.window.escape="handleEscapeKey()"
             class="stopwatch-modal-box">

            {{-- Brand Navy Top Accent Stripe --}}
            <div class="absolute top-0 inset-x-0 h-1 bg-[#1B3A6B]"></div>

            {{-- Modal Topbar --}}
            <div class="relative flex items-center justify-between px-6 pt-5 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-[#1B3A6B] border border-blue-100/80">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="12" cy="13" r="8"></circle>
                            <path d="M12 9v4l2.5 2.5"></path>
                            <path d="M10 2h4M12 2v3"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Record Shoes Mini-Game</h3>
                        <p class="text-[11px] font-medium text-slate-500">Stopwatch 10.00s Challenge</p>
                    </div>
                </div>

                {{-- Close Button --}}
                <button @click="confirmClose()" 
                        type="button" 
                        class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition-colors cursor-pointer"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 1: INTRO & RULES                                         --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'intro'" class="p-6 sm:p-7 flex flex-col items-center text-center">
                
                {{-- Hero Visual Icon --}}
                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#1B3A6B] shadow-sm mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="13" r="8"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4l2.5 2.5"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 2h4M12 2v3"></path>
                    </svg>
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-2">
                    Tantangan Tepat 10.00 Detik!
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-sm mb-6">
                    Hentikan stopwatch seakurat mungkin di angka <strong class="text-[#1B3A6B] font-bold">10.00 detik</strong>. Menangkan voucher belanja resmi Record Shoes!
                </p>

                {{-- Hadiah Tier Badges --}}
                <div class="w-full grid grid-cols-3 gap-2.5 mb-6 text-left">
                    {{-- Tier Perfect --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="inline-block w-2 h-2 rounded-full bg-[#1B3A6B]"></span>
                                <span class="text-[10px] font-bold uppercase text-slate-900 tracking-wider">Perfect</span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">
                                @if(abs($perfectMin - 10.00) < 0.001 && abs($perfectMax - 10.00) < 0.001)
                                    10.00s pas
                                @else
                                    {{ number_format($perfectMin, 2) }}s - {{ number_format($perfectMax, 2) }}s
                                @endif
                            </p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-slate-200/80">
                            <span class="block text-xs font-black text-[#1B3A6B]">Rp {{ number_format($perfectAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Tier Near --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                <span class="text-[10px] font-bold uppercase text-slate-900 tracking-wider">Nyaris</span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">{{ number_format($nearMin, 2) }}s - {{ number_format($nearMax, 2) }}s</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-slate-200/80">
                            <span class="block text-xs font-black text-slate-800">Rp {{ number_format($nearAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Tier Good --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="inline-block w-2 h-2 rounded-full bg-slate-400"></span>
                                <span class="text-[10px] font-bold uppercase text-slate-900 tracking-wider">Partisipasi</span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">Selain itu</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-slate-200/80">
                            <span class="block text-xs font-black text-slate-800">Rp {{ number_format($goodAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Aturan Ringkas --}}
                <div class="w-full bg-slate-50 rounded-xl p-3.5 border border-slate-200/90 mb-6 text-left">
                    <ul class="text-xs text-slate-600 space-y-2">
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#1B3A6B] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ $swDailyLimit }}x kesempatan bermain per akun setiap harinya.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#1B3A6B] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Voucher diambil langsung dari stok aktif Kelola Voucher.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#1B3A6B] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tekan <strong class="text-slate-900">Simpan</strong> setelah menang, atau voucher akan hangus!</span>
                        </li>
                    </ul>
                </div>

                {{-- Action CTA: Tombol Navy Blue --}}
                <template x-if="!isAuthenticated">
                    <div class="w-full">
                        <a href="{{ route('login') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-[#1B3A6B] hover:bg-[#2A4F8F] active:bg-[#122850] text-white font-bold text-sm tracking-wide shadow-md shadow-blue-900/15 active:scale-[0.99] transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            LOGIN UNTUK BERMAIN
                        </a>
                        <p class="text-xs text-slate-500 mt-2.5">Belum punya akun? <a href="{{ route('register') }}" class="text-[#1B3A6B] underline font-bold hover:text-blue-800">Daftar di sini</a></p>
                    </div>
                </template>

                <template x-if="isAuthenticated && !canPlay">
                    <div class="w-full bg-amber-50 border border-amber-200/80 rounded-xl p-4 text-center">
                        <div class="flex items-center justify-center gap-2 text-amber-900 font-bold text-xs mb-1">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Kesempatan Hari Ini Sudah Digunakan
                        </div>
                        <p class="text-xs text-amber-800" x-text="statusMessage"></p>
                    </div>
                </template>

                <template x-if="isAuthenticated && canPlay">
                    <button @click="startGame()" 
                            type="button" 
                            class="w-full py-3.5 px-6 rounded-xl bg-[#1B3A6B] hover:bg-[#2A4F8F] active:bg-[#122850] text-white font-bold text-sm tracking-wide shadow-md shadow-blue-900/15 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center gap-2">
                        <span>MULAI TANTANGAN SEKARANG</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </template>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 2: GAMEPLAY (STOPWATCH RUNNING)                          --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'running'" class="p-6 sm:p-8 flex flex-col items-center text-center">
                
                {{-- Target Indicator --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-50 border border-blue-200/80 text-[#1B3A6B] text-xs font-bold tracking-wide uppercase mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#1B3A6B] animate-ping"></span>
                    Target: 10.00 Detik
                </div>

                {{-- Digital Chronometer Display --}}
                <div class="relative w-64 h-64 sm:w-72 sm:h-72 flex items-center justify-center mb-6">
                    
                    {{-- SVG Animated Progress Dial --}}
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        {{-- Track background --}}
                        <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="4" class="text-slate-100" fill="transparent" />
                        {{-- Running line in Navy Blue --}}
                        <circle cx="50" cy="50" r="42" 
                                stroke="#1B3A6B" 
                                stroke-width="5" 
                                stroke-linecap="round"
                                :stroke-dasharray="264"
                                :stroke-dashoffset="264 - (progressPercent / 100 * 264)"
                                fill="transparent"
                                class="transition-all duration-75 ease-linear" />
                    </svg>

                    {{-- Inner Digital Numerals --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-mono text-5xl sm:text-6xl font-black tracking-tight text-slate-900 select-none tabular-nums"
                              x-text="displaySeconds + '.' + displayMillis">
                            00.00
                        </span>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-slate-400 mt-1">Detik</span>
                        
                        {{-- Realtime Proximity Hint --}}
                        <div class="mt-2 h-5 flex items-center">
                            <template x-if="elapsed >= 9.5 && elapsed <= 10.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white animate-bounce tracking-wider">
                                    SEKARANG!!
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-500 mb-6">
                    Fokus dan klik tombol STOP di bawah (atau tekan <kbd class="px-2 py-0.5 rounded bg-slate-100 border border-slate-300 text-slate-700 font-mono text-[10px] font-semibold">SPASI</kbd> di keyboard)!
                </p>

                {{-- Action Button: STOP! --}}
                <button @click="stopGame()" 
                        type="button" 
                        class="w-full sm:w-64 py-3.5 px-8 rounded-full bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-black text-base tracking-widest uppercase shadow-lg shadow-rose-600/25 transition-all duration-150 cursor-pointer flex items-center justify-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-white animate-ping"></span>
                    <span>STOP!</span>
                </button>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 3: RESULT & VOUCHER TICKET                              --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'result'" class="p-6 sm:p-7 flex flex-col items-center text-center">
                
                {{-- Result Time Header --}}
                <div class="mb-4">
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 text-xs font-mono font-medium mb-2">
                        Waktu Kamu: <span class="text-slate-900 font-bold" x-text="finalTime + ' detik'"></span>
                        <span class="text-[11px] font-bold text-[#1B3A6B]" x-text="'(' + (timeDiff >= 0 ? '+' : '') + timeDiff + 's)'"></span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight" x-text="tierResult?.label || 'Hasil Permainan'"></h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1" x-text="tierResult?.title || ''"></p>
                </div>

                {{-- JIKA VOUCHER TERSEDIA --}}
                <template x-if="hasVoucher && voucher">
                    <div class="w-full my-2">
                        
                        {{-- ── LUXURY TICKET COMPONENT ── --}}
                        <div class="luxury-ticket relative w-full rounded-2xl overflow-hidden border border-slate-200 shadow-sm"
                             :class="{
                                 'ticket--gold': tierKey === 'perfect',
                                 'ticket--violet': tierKey === 'near',
                                 'ticket--emerald': tierKey === 'good'
                             }">
                            
                            {{-- Notches (Kiri & Kanan Cutouts) --}}
                            <div class="ticket-notch ticket-notch--left"></div>
                            <div class="ticket-notch ticket-notch--right"></div>

                            {{-- Ticket Content --}}
                            <div class="relative p-5 sm:p-6 text-slate-900 z-10">
                                
                                {{-- Brand Watermark Header --}}
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold tracking-widest uppercase text-slate-900">RECORD SHOES</span>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-[#1B3A6B]/10 text-[#1B3A6B]">OFFICIAL VOUCHER</span>
                                    </div>
                                    <span class="text-[11px] font-mono font-semibold text-slate-500">#<span x-text="voucher.code"></span></span>
                                </div>

                                {{-- Voucher Value --}}
                                <div class="py-4 text-center">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                                        Potongan Belanja Langsung
                                    </span>
                                    <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight" x-text="voucher.formatted_amount">
                                        Rp {{ $maxVoucherFormatted }}
                                    </h3>
                                    <div class="mt-2 flex items-center justify-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition-all"
                                              :class="voucher.category_name ? 'bg-blue-50 text-[#1B3A6B] border-blue-200 shadow-sm' : 'bg-slate-100 text-slate-700 border-slate-200'"
                                              x-text="voucher.scope_label || 'Berlaku untuk: Semua Produk'">
                                        </span>
                                    </div>
                                </div>

                                {{-- Dashed Perforation Line --}}
                                <div class="relative my-2 border-t-2 border-dashed border-slate-300"></div>

                                {{-- Voucher Code Box & Copy Button --}}
                                <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <div class="flex flex-col items-start w-full sm:w-auto">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kode Voucher Kamu:</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="font-mono text-xl sm:text-2xl font-black tracking-[0.2em] bg-white border border-slate-300 text-slate-900 px-3 py-1 rounded-lg select-all shadow-sm" 
                                                  x-text="voucher.code">
                                            </span>
                                            <button @click="copyCode(voucher.code)" 
                                                    type="button" 
                                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                                    :title="copied ? 'Tersalin!' : 'Salin Kode'">
                                                <template x-if="!copied">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </template>
                                                <template x-if="copied">
                                                    <span class="text-[11px] font-bold text-emerald-600">Tersalin ✓</span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Countdown Info --}}
                                    <div class="text-right sm:text-right w-full sm:w-auto">
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                                            <svg class="w-3.5 h-3.5 text-rose-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Reservasi: 5 Menit
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Peringatan Simpan --}}
                        <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs text-left flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="leading-relaxed text-[11px] text-amber-800">
                                <strong>Penting:</strong> Tekan tombol <strong>"Simpan Voucher ke Akun Saya"</strong> di bawah sekarang. Jika kamu menutup jendela ini tanpa menyimpan, voucher akan otomatis hangus!
                            </p>
                        </div>

                        {{-- Action Buttons: Tombol Simpan Navy Blue --}}
                        <div class="mt-5 flex flex-col gap-2.5">
                            <button @click="saveVoucher()" 
                                    :disabled="saving"
                                    type="button" 
                                    class="w-full py-3.5 px-6 rounded-xl bg-[#1B3A6B] hover:bg-[#2A4F8F] active:bg-[#122850] text-white font-bold text-sm tracking-wide shadow-md shadow-blue-900/15 active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center gap-2">
                                <template x-if="!saving">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        SIMPAN VOUCHER KE AKUN SAYA
                                    </span>
                                </template>
                                <template x-if="saving">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Menyimpan...
                                    </span>
                                </template>
                            </button>

                            <button @click="discardVoucher()" 
                                    type="button" 
                                    class="w-full py-2.5 px-4 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer">
                                Tutup (Hanguskan Voucher Ini)
                            </button>
                        </div>
                    </div>
                </template>

                {{-- JIKA VOUCHER KOSONG DI DATABASE ADMIN --}}
                <template x-if="!hasVoucher">
                    <div class="w-full my-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-2xl">
                            📦
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 mb-1">Stok Voucher Sedang Kosong</h4>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-sm mx-auto mb-4" x-text="emptyMessage">
                            Stok voucher untuk nominal ini di Kelola Voucher sedang kosong. Nantikan restock batch berikutnya!
                        </p>
                        <button @click="closeModal()" 
                                type="button" 
                                class="py-2.5 px-6 rounded-xl bg-[#1B3A6B] hover:bg-[#2A4F8F] text-white text-xs font-bold transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </template>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 4: SUCCESS / SAVED                                       --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'saved'" class="p-6 sm:p-8 flex flex-col items-center text-center">
                
                {{-- Success Icon --}}
                <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shadow-sm mb-4">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-2">
                    Voucher Berhasil Disimpan! 🎉
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-sm mb-6">
                    Selamat! Voucher diskon telah tersimpan aman di akun kamu dan siap digunakan saat checkout.
                </p>

                {{-- Voucher Summary Box --}}
                <template x-if="voucher">
                    <div class="w-full bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 flex flex-col gap-2.5">
                        <div class="flex items-center justify-between">
                            <div class="text-left">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Kode Diskon</span>
                                <span class="font-mono text-xl font-black text-[#1B3A6B] tracking-wider" x-text="voucher.code"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Nominal</span>
                                <span class="text-sm font-black text-slate-900" x-text="voucher.formatted_amount"></span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between text-left">
                            <span class="text-[11px] font-bold"
                                  :class="voucher.category_name ? 'text-blue-700' : 'text-slate-600'"
                                  x-text="voucher.scope_label || 'Berlaku untuk: Semua Produk'"></span>
                            <span class="text-[10px] text-slate-400 font-medium">Maks. 1 voucher / order</span>
                        </div>
                    </div>
                </template>

                {{-- Action Links --}}
                <div class="w-full flex flex-col sm:flex-row gap-3">
                    <button @click="copyCode(voucher?.code)" 
                            type="button" 
                            class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition cursor-pointer flex items-center justify-center gap-1.5">
                        <template x-if="!copied">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Salin Kode Voucher
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="text-emerald-600 font-extrabold">Kode Tersalin! ✓</span>
                        </template>
                    </button>

                    <button @click="closeModal()" 
                            type="button" 
                            class="flex-1 py-3 px-4 rounded-xl bg-[#1B3A6B] hover:bg-[#2A4F8F] active:bg-[#122850] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-blue-900/15 transition cursor-pointer">
                        Mulai Belanja
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 3. LUXURY TICKET CSS STYLES                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <style>
        /* ── Floating Trigger & Button ── */
        .stopwatch-floating-trigger {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999999;
            display: flex;
            align-items: center;
        }

        .stopwatch-floating-btn {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px 10px 12px;
            border-radius: 9999px;
            background: #1B3A6B;
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 10px 25px -4px rgba(27, 58, 107, 0.4), 0 4px 10px -2px rgba(27, 58, 107, 0.2);
            cursor: pointer;
            text-decoration: none;
        }

        .stopwatch-floating-btn:hover {
            background: #2A4F8F;
            box-shadow: 0 12px 28px -4px rgba(27, 58, 107, 0.5);
        }

        /* ── Modal Backdrop & Box ── */
        .stopwatch-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-y: auto;
        }

        .stopwatch-modal-box {
            position: relative;
            width: 100%;
            max-width: 480px;
            margin: auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            z-index: 10;
            display: flex;
            flex-direction: column;
        }

        /* ── Notch Cutouts (Sobekan Tiket Fisik) ── */
        .luxury-ticket {
            position: relative;
            background: #ffffff;
        }
        .luxury-ticket .ticket-notch {
            position: absolute;
            top: 50%;
            width: 22px;
            height: 22px;
            background: #ffffff !important; /* Latar belakang putih sesuai modal */
            border-radius: 50%;
            transform: translateY(-50%);
            z-index: 20;
        }
        .luxury-ticket .ticket-notch--left {
            left: -11px;
            box-shadow: inset -1.5px 0 3px rgba(0,0,0,0.06);
            border-right: 1px solid #e2e8f0;
        }
        .luxury-ticket .ticket-notch--right {
            right: -11px;
            box-shadow: inset 1.5px 0 3px rgba(0,0,0,0.06);
            border-left: 1px solid #e2e8f0;
        }

        /* ── Tier Themes (Elegan & Anti AI-Slop) ── */
        /* Gold Tier (Perfect 50k) */
        .luxury-ticket.ticket--gold {
            background: linear-gradient(135deg, #fffdf5 0%, #fef9e7 50%, #fef3c7 100%);
            border: 1px solid #fde68a;
        }

        /* Blue Tier (Near Miss 30k) */
        .luxury-ticket.ticket--violet {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 50%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
        }

        /* Neutral Tier (Good Try 10k) */
        .luxury-ticket.ticket--emerald {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
        }

        @media (max-width: 640px) {
            .stopwatch-floating-trigger {
                bottom: 16px;
                right: 12px;
            }
            .stopwatch-floating-btn {
                padding: 8px 14px 8px 10px;
                gap: 8px;
            }
        }
    </style>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 4. ALPINE.JS COMPONENT LOGIC                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <script>
    (function () {
        function initStopwatchComponent() {
            if (typeof Alpine === 'undefined') return;

            Alpine.data('gameStopwatchWidget', () => ({
                modalOpen: false,
                screen: 'intro', // 'intro' | 'running' | 'result' | 'saved'
                isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
                canPlay: true,
                statusMessage: '',
                
                // Stopwatch Running State
                startTime: null,
                elapsed: 0,
                displaySeconds: '00',
                displayMillis: '00',
                progressPercent: 0,
                rafId: null,

                // Result State
                finalTime: '00.00',
                timeDiff: 0,
                tierKey: 'good',
                tierResult: null,
                hasVoucher: false,
                emptyMessage: '',
                voucher: null,

                // UX State
                saving: false,
                copied: false,

                // Init & Check Status
                initWidget() {
                    this.fetchStatus();

                    // Spacebar shortcut to STOP during running screen
                    window.addEventListener('keydown', (e) => {
                        if (this.modalOpen && this.screen === 'running' && e.code === 'Space') {
                            e.preventDefault();
                            this.stopGame();
                        }
                    });
                },

                fetchStatus() {
                    fetch('{{ route("game.stopwatch.status") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.canPlay = !!data.can_play;
                        this.isAuthenticated = !!data.is_authenticated;
                        if (data.message) {
                            this.statusMessage = data.message;
                        }
                    })
                    .catch(() => {});
                },

                openModal() {
                    this.modalOpen = true;
                    if (this.screen !== 'result' && this.screen !== 'saved') {
                        this.screen = 'intro';
                        this.fetchStatus();
                    }
                },

                closeModal() {
                    this.modalOpen = false;
                },

                handleBackdropClick() {
                    this.confirmClose();
                },

                handleEscapeKey() {
                    this.confirmClose();
                },

                confirmClose() {
                    // Jika sedang di screen result dan memiliki voucher yang belum disimpan
                    if (this.screen === 'result' && this.hasVoucher && this.voucher) {
                        const konfirmasi = confirm('Perhatian: Jika kamu menutup jendela ini sekarang, voucher Rp ' + this.voucher.formatted_amount + ' akan hangus dan tidak dapat diklaim lagi. Yakin ingin keluar?');
                        if (!konfirmasi) {
                            return;
                        }
                        this.discardVoucher();
                        return;
                    }

                    // Jika stopwatch sedang berjalan, hentikan dulu
                    if (this.screen === 'running') {
                        cancelAnimationFrame(this.rafId);
                    }

                    this.modalOpen = false;
                },

                startGame() {
                    if (!this.isAuthenticated || !this.canPlay) return;

                    this.screen = 'running';
                    this.elapsed = 0;
                    this.displaySeconds = '00';
                    this.displayMillis = '00';
                    this.progressPercent = 0;
                    this.startTime = performance.now();

                    const tick = (now) => {
                        if (this.screen !== 'running') return;

                        const ms = now - this.startTime;
                        this.elapsed = ms / 1000;

                        // Auto stop jika lewat dari 20 detik
                        if (this.elapsed >= 20.0) {
                            this.stopGame();
                            return;
                        }

                        // Format tampilan
                        const sec = Math.floor(this.elapsed);
                        const centi = Math.floor((this.elapsed % 1) * 100);

                        this.displaySeconds = sec.toString().padStart(2, '0');
                        this.displayMillis = centi.toString().padStart(2, '0');

                        // Progress bar dial (0-100% sampai target 10s, max 100%)
                        this.progressPercent = Math.min(100, (this.elapsed / 10.0) * 100);

                        this.rafId = requestAnimationFrame(tick);
                    };

                    this.rafId = requestAnimationFrame(tick);
                },

                stopGame() {
                    if (this.screen !== 'running') return;
                    cancelAnimationFrame(this.rafId);

                    const stopTime = performance.now();
                    const exactElapsed = (stopTime - this.startTime) / 1000;
                    const finalElapsed = Math.round(exactElapsed * 100) / 100;

                    this.finalTime = finalElapsed.toFixed(2);
                    this.timeDiff = Math.round((finalElapsed - 10.00) * 100) / 100;

                    // Kirim ke server
                    this.submitClaim(finalElapsed);
                },

                submitClaim(elapsedTime) {
                    fetch('{{ route("game.stopwatch.claim") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ elapsed_time: elapsedTime })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success && data.message) {
                            alert(data.message);
                            this.screen = 'intro';
                            this.fetchStatus();
                            return;
                        }

                        this.canPlay = false;
                        this.hasVoucher = !!data.has_voucher;
                        this.tierKey = data.tier_key || 'good';
                        this.tierResult = {
                            label: data.tier,
                            title: data.tier_title,
                        };

                        if (this.hasVoucher && data.voucher) {
                            this.voucher = data.voucher;
                            this.triggerConfetti();
                        } else {
                            this.emptyMessage = data.message || 'Stok voucher sedang kosong.';
                        }

                        this.screen = 'result';
                    })
                    .catch(err => {
                        console.error('Claim error:', err);
                        alert('Terjadi kendala saat memproses hasil game.');
                        this.screen = 'intro';
                    });
                },

                saveVoucher() {
                    if (!this.voucher?.code || this.saving) return;

                    this.saving = true;
                    fetch('{{ route("game.stopwatch.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ voucher_code: this.voucher.code })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.saving = false;
                        if (data.success) {
                            this.screen = 'saved';
                            this.triggerConfetti();
                        } else {
                            alert(data.message || 'Gagal menyimpan voucher.');
                        }
                    })
                    .catch(err => {
                        this.saving = false;
                        console.error('Save error:', err);
                        alert('Gagal menyimpan voucher ke akun.');
                    });
                },

                discardVoucher() {
                    const code = this.voucher?.code;
                    if (code) {
                        fetch('{{ route("game.stopwatch.discard") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ voucher_code: code })
                        }).catch(() => {});
                    }

                    this.voucher = null;
                    this.hasVoucher = false;
                    this.modalOpen = false;
                    this.screen = 'intro';
                },

                copyCode(code) {
                    if (!code) return;
                    navigator.clipboard.writeText(code).then(() => {
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2500);
                    }).catch(() => {});
                },

                triggerConfetti() {
                    const fire = () => {
                        if (window.confetti) {
                            window.confetti({
                                particleCount: 80,
                                spread: 70,
                                origin: { y: 0.6 }
                            });
                        }
                    };

                    if (!window.confetti) {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js';
                        s.onload = fire;
                        document.head.appendChild(s);
                    } else {
                        fire();
                    }
                }
            }));
        }

        if (window.Alpine) {
            initStopwatchComponent();
        } else {
            document.addEventListener('alpine:init', initStopwatchComponent);
        }
    })();
    </script>
</div>
@endif
