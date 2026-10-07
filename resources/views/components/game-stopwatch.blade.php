{{--
    Record Shoes - Stopwatch Challenge Mini-Game Widget
    Komponen interaktif berhadiah voucher diskon (10.00 detik pas = 50rb, mendekati = 30rb, lainnya = 10rb)
    Support Responsive HP & Desktop, Luxury Ticket Design, Confetti, Alpine.js State
--}}

<div x-data="gameStopwatchWidget()" 
     x-init="initWidget()"
     class="stopwatch-game-root">

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 1. FLOATING ACTION BUTTON (Pojok Kanan Bawah)                      --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="!modalOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="fixed bottom-5 right-4 sm:bottom-6 sm:right-6 z-[9999] flex items-center gap-2 group"
         style="z-index: 9999;">
        
        {{-- Floating Pill Button --}}
        <button @click="openModal()" 
                type="button"
                aria-label="Buka Tantangan Stopwatch 10 Detik"
                class="relative flex items-center gap-3.5 pl-4 pr-5 py-3 rounded-full bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 border border-amber-500/40 text-white shadow-[0_8px_30px_rgba(245,158,11,0.25)] hover:shadow-[0_12px_40px_rgba(245,158,11,0.45)] hover:border-amber-400 active:scale-95 transition-all duration-300 cursor-pointer overflow-hidden">
            
            {{-- Glowing Background Pulse --}}
            <span class="absolute inset-0 bg-gradient-to-r from-amber-500/10 via-amber-400/20 to-amber-500/10 opacity-75 group-hover:opacity-100 transition-opacity"></span>
            <span class="absolute -inset-px rounded-full border border-amber-400/30 animate-pulse pointer-events-none"></span>

            {{-- Icon Stopwatch with Ping Ring --}}
            <div class="relative flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 shadow-md flex-shrink-0">
                <svg class="w-5 h-5 animate-[spin_8s_linear_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="13" r="8"></circle>
                    <path d="M12 9v4l2.5 2.5"></path>
                    <path d="M10 2h4M12 2v3"></path>
                </svg>
                {{-- Tiny Sparkle Badge --}}
                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-amber-500 border border-white"></span>
                </span>
            </div>

            {{-- Text Label --}}
            <div class="flex flex-col text-left">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-400">10.00s Challenge</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">Voucher</span>
                </div>
                <span class="text-xs sm:text-sm font-extrabold text-white tracking-tight flex items-center gap-1">
                    Dapatkan s/d Rp 50.000
                    <svg class="w-3.5 h-3.5 text-amber-400 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            </div>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- 2. MODAL OVERLAY & CONTAINER                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-md overflow-y-auto" style="z-index: 99999;">

        {{-- Backdrop click (hanya bisa tutup jika bukan saat holding voucher) --}}
        <div class="fixed inset-0" @click="handleBackdropClick()"></div>

        {{-- Modal Box --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @keydown.window.escape="handleEscapeKey()"
             class="relative w-full max-w-lg my-auto bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border border-slate-800 text-white rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.7)] overflow-hidden z-10 flex flex-col">

            {{-- Decorative Header Glow Accent --}}
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 via-violet-500 to-emerald-500"></div>
            <div class="absolute top-0 right-1/4 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Modal Topbar --}}
            <div class="relative flex items-center justify-between px-6 pt-5 pb-3 border-b border-slate-800/80">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="12" cy="13" r="8"></circle>
                            <path d="M12 9v4l2.5 2.5"></path>
                            <path d="M10 2h4M12 2v3"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-300">Record Shoes Mini-Game</h3>
                        <p class="text-[10px] font-bold text-amber-400">Stopwatch 10.00s Challenge</p>
                    </div>
                </div>

                {{-- Close Button --}}
                <button @click="confirmClose()" 
                        type="button" 
                        class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 active:scale-95 transition-all cursor-pointer"
                        title="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 1: INTRO & RULES                                         --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'intro'" class="p-6 sm:p-7 flex flex-col items-center text-center">
                
                {{-- Hero Visual --}}
                <div class="relative w-20 h-20 mb-4 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 opacity-20 blur-xl"></div>
                    <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-amber-500/40 flex items-center justify-center shadow-inner">
                        <span class="text-3xl">⏱️</span>
                    </div>
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight mb-2">
                    Tantangan Tepat 10.00 Detik!
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm mb-6">
                    Hentikan stopwatch seakurat mungkin di angka <strong class="text-amber-400 font-extrabold">10.00 detik</strong>. Menangkan voucher belanja resmi Record Shoes!
                </p>

                {{-- Hadiah Tier Badges --}}
                <div class="w-full grid grid-cols-3 gap-2.5 mb-6 text-left">
                    {{-- Tier Perfect --}}
                    <div class="p-3 rounded-2xl bg-gradient-to-b from-amber-500/10 to-slate-900/60 border border-amber-500/30 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-1 mb-1">
                                <span class="text-xs">🎯</span>
                                <span class="text-[10px] font-black uppercase text-amber-400 tracking-wider">Perfect</span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium">10.00s pas</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-amber-500/20">
                            <span class="block text-xs font-black text-amber-300">Rp 50.000</span>
                        </div>
                    </div>

                    {{-- Tier Near --}}
                    <div class="p-3 rounded-2xl bg-gradient-to-b from-violet-500/10 to-slate-900/60 border border-violet-500/30 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-1 mb-1">
                                <span class="text-xs">⚡</span>
                                <span class="text-[10px] font-black uppercase text-violet-400 tracking-wider">Nyaris</span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium">9.85s - 10.15s</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-violet-500/20">
                            <span class="block text-xs font-black text-violet-300">Rp 30.000</span>
                        </div>
                    </div>

                    {{-- Tier Good --}}
                    <div class="p-3 rounded-2xl bg-gradient-to-b from-emerald-500/10 to-slate-900/60 border border-emerald-500/30 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-1 mb-1">
                                <span class="text-xs">🎁</span>
                                <span class="text-[10px] font-black uppercase text-emerald-400 tracking-wider">Partisipasi</span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium">Selain itu</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-emerald-500/20">
                            <span class="block text-xs font-black text-emerald-300">Rp 10.000</span>
                        </div>
                    </div>
                </div>

                {{-- Aturan Ringkas --}}
                <div class="w-full bg-slate-950/60 rounded-xl p-3 border border-slate-800/80 mb-6 text-left">
                    <ul class="text-[11px] text-slate-400 space-y-1.5">
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>1x kesempatan bermain per akun setiap harinya.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Voucher diambil langsung dari stok aktif Kelola Voucher.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Tekan <strong>Simpan</strong> setelah menang, atau voucher akan hangus!</span>
                        </li>
                    </ul>
                </div>

                {{-- Action CTA --}}
                <template x-if="!isAuthenticated">
                    <div class="w-full">
                        <a href="{{ route('login') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-amber-500/20 active:scale-[0.98] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Login untuk Bermain
                        </a>
                        <p class="text-[11px] text-slate-500 mt-2">Belum punya akun? <a href="{{ route('register') }}" class="text-amber-400 underline font-semibold">Daftar di sini</a></p>
                    </div>
                </template>

                <template x-if="isAuthenticated && !canPlay">
                    <div class="w-full bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 text-center">
                        <div class="flex items-center justify-center gap-2 text-amber-400 font-bold text-xs mb-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Kesempatan Hari Ini Sudah Digunakan
                        </div>
                        <p class="text-xs text-slate-400" x-text="statusMessage"></p>
                    </div>
                </template>

                <template x-if="isAuthenticated && canPlay">
                    <button @click="startGame()" 
                            type="button" 
                            class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-sm uppercase tracking-wider shadow-[0_10px_30px_rgba(245,158,11,0.35)] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                        <span>MULAI TANTANGAN SEKARANG</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </template>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 2: GAMEPLAY (STOPWATCH RUNNING)                          --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'running'" class="p-6 sm:p-8 flex flex-col items-center text-center">
                
                {{-- Target Indicator --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-black tracking-wider uppercase mb-6">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    Target: 10.00 Detik
                </div>

                {{-- Digital Chronometer Display --}}
                <div class="relative w-64 h-64 sm:w-72 sm:h-72 flex items-center justify-center mb-6">
                    
                    {{-- SVG Animated Progress Dial --}}
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        {{-- Track background --}}
                        <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="4" class="text-slate-800" fill="transparent" />
                        {{-- Running line --}}
                        <circle cx="50" cy="50" r="42" 
                                stroke="url(#stopwatchGrad)" 
                                stroke-width="5" 
                                stroke-linecap="round"
                                :stroke-dasharray="264"
                                :stroke-dashoffset="264 - (progressPercent / 100 * 264)"
                                fill="transparent"
                                class="transition-all duration-75 ease-linear" />
                        
                        <defs>
                            <linearGradient id="stopwatchGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#f59e0b" />
                                <stop offset="50%" stop-color="#8b5cf6" />
                                <stop offset="100%" stop-color="#10b981" />
                            </linearGradient>
                        </defs>
                    </svg>

                    {{-- Inner Digital Numerals --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-mono text-5xl sm:text-6xl font-extrabold tracking-wider text-white select-none tabular-nums"
                              x-text="displaySeconds + '.' + displayMillis">
                            00.00
                        </span>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-slate-400 mt-1">Detik</span>
                        
                        {{-- Realtime Proximity Hint --}}
                        <div class="mt-2 h-5 flex items-center">
                            <template x-if="elapsed >= 9.5 && elapsed <= 10.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500 text-slate-950 animate-bounce">
                                    SEKARANG!!
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-6">
                    Fokus dan klik tombol merah di bawah (atau tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[10px]">SPASI</kbd> di keyboard)!
                </p>

                {{-- Big Action Button: STOP! --}}
                <button @click="stopGame()" 
                        type="button" 
                        class="w-full sm:w-64 py-4 px-8 rounded-full bg-gradient-to-r from-red-600 via-rose-500 to-red-600 hover:from-red-500 hover:to-rose-400 active:scale-95 text-white font-black text-lg tracking-widest uppercase shadow-[0_10px_35px_rgba(239,68,68,0.5)] transition duration-150 cursor-pointer flex items-center justify-center gap-3">
                    <span class="w-3.5 h-3.5 rounded-full bg-white animate-ping"></span>
                    <span>STOP!</span>
                </button>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SCREEN 3: RESULT & VOUCHER TICKET                              --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div x-show="screen === 'result'" class="p-6 sm:p-7 flex flex-col items-center text-center">
                
                {{-- Result Time Header --}}
                <div class="mb-4">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-mono font-bold mb-2">
                        Waktu Kamu: <span class="text-white font-extrabold" x-text="finalTime + ' detik'"></span>
                        <span class="text-[10px]" :class="timeDiff >= 0 ? 'text-amber-400' : 'text-cyan-400'" x-text="'(' + (timeDiff >= 0 ? '+' : '') + timeDiff + 's)'"></span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight" x-text="tierResult?.label || 'Hasil Permainan'"></h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1" x-text="tierResult?.title || ''"></p>
                </div>

                {{-- JIKA VOUCHER TERSEDIA --}}
                <template x-if="hasVoucher && voucher">
                    <div class="w-full my-2">
                        
                        {{-- ── LUXURY TICKET COMPONENT ── --}}
                        <div class="luxury-ticket relative w-full rounded-2xl overflow-hidden shadow-2xl"
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
                                <div class="flex items-center justify-between pb-3 border-b border-black/10">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black tracking-widest uppercase">RECORD SHOES</span>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-black/10">OFFICIAL VOUCHER</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold opacity-75">#<span x-text="voucher.code"></span></span>
                                </div>

                                {{-- Voucher Value --}}
                                <div class="py-4 text-center">
                                    <span class="block text-[11px] font-bold uppercase tracking-widest text-black/70 mb-1">
                                        Potongan Belanja Langsung
                                    </span>
                                    <h3 class="text-3xl sm:text-4xl font-black tracking-tight" x-text="voucher.formatted_amount">
                                        Rp 50.000
                                    </h3>
                                    <p class="text-[10px] font-semibold text-black/60 mt-1">
                                        Berlaku untuk semua produk di checkout
                                    </p>
                                </div>

                                {{-- Dashed Perforation Line --}}
                                <div class="relative my-2 border-t-2 border-dashed border-black/20"></div>

                                {{-- Voucher Code Box & Copy Button --}}
                                <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <div class="flex flex-col items-start w-full sm:w-auto">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-black/60">Kode Voucher Kamu:</span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-xl sm:text-2xl font-black tracking-[0.25em] bg-black/10 px-3 py-1 rounded-lg select-all" 
                                                  x-text="voucher.code">
                                            </span>
                                            <button @click="copyCode(voucher.code)" 
                                                    type="button" 
                                                    class="px-2.5 py-1.5 rounded-lg bg-black/15 hover:bg-black/25 text-black text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                                    :title="copied ? 'Tersalin!' : 'Salin Kode'">
                                                <template x-if="!copied">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </template>
                                                <template x-if="copied">
                                                    <span class="text-[10px] font-black text-emerald-800">Tersalin ✓</span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Countdown Info --}}
                                    <div class="text-right sm:text-right w-full sm:w-auto">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-black/70 bg-black/10 px-2 py-1 rounded">
                                            <svg class="w-3 h-3 text-red-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Reservasi: 5 Menit
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Peringatan Simpan --}}
                        <div class="mt-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs text-left flex items-start gap-2.5">
                            <span class="text-sm">⚠️</span>
                            <p class="leading-relaxed text-[11px]">
                                <strong>Penting:</strong> Tekan tombol <strong>"Simpan ke Akun"</strong> di bawah sekarang. Jika kamu menutup jendela ini tanpa menyimpan, voucher akan otomatis hangus!
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-5 flex flex-col gap-2.5">
                            <button @click="saveVoucher()" 
                                    :disabled="saving"
                                    type="button" 
                                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-black text-sm uppercase tracking-wider shadow-[0_10px_25px_rgba(16,185,129,0.35)] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                                <template x-if="!saving">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        SIMPAN VOUCHER KE AKUN SAYA
                                    </span>
                                </template>
                                <template x-if="saving">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Menyimpan...
                                    </span>
                                </template>
                            </button>

                            <button @click="discardVoucher()" 
                                    type="button" 
                                    class="w-full py-2.5 px-4 rounded-xl text-slate-400 hover:text-red-400 hover:bg-slate-800/60 text-xs font-semibold transition cursor-pointer">
                                Tutup (Hanguskan Voucher Ini)
                            </button>
                        </div>
                    </div>
                </template>

                {{-- JIKA VOUCHER KOSONG DI DATABASE ADMIN --}}
                <template x-if="!hasVoucher">
                    <div class="w-full my-4 p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-700/60 flex items-center justify-center mx-auto mb-3 text-2xl">
                            📦
                        </div>
                        <h4 class="text-sm font-bold text-white mb-1">Stok Voucher Sedang Kosong</h4>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-sm mx-auto mb-4" x-text="emptyMessage">
                            Stok voucher untuk nominal ini di Kelola Voucher sedang kosong. Nantikan restock batch berikutnya!
                        </p>
                        <button @click="closeModal()" 
                                type="button" 
                                class="py-2.5 px-6 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold transition cursor-pointer">
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
                <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-emerald-500 opacity-20 blur-xl animate-pulse"></div>
                    <div class="relative w-14 h-14 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-slate-950 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight mb-2">
                    Voucher Berhasil Disimpan! 🎉
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-sm mb-6">
                    Selamat! Voucher diskon telah tersimpan aman di akun kamu dan siap digunakan saat checkout.
                </p>

                {{-- Voucher Summary Box --}}
                <template x-if="voucher">
                    <div class="w-full bg-slate-950/80 rounded-2xl p-4 border border-emerald-500/30 mb-6 flex items-center justify-between">
                        <div class="text-left">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kode Diskon</span>
                            <span class="font-mono text-xl font-extrabold text-emerald-400 tracking-wider" x-text="voucher.code"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nominal</span>
                            <span class="text-sm font-extrabold text-white" x-text="voucher.formatted_amount"></span>
                        </div>
                    </div>
                </template>

                {{-- Action Links --}}
                <div class="w-full flex flex-col sm:flex-row gap-3">
                    <button @click="copyCode(voucher?.code)" 
                            type="button" 
                            class="flex-1 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                        <template x-if="!copied">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Salin Kode Voucher
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="text-emerald-400 font-extrabold">Kode Tersalin! ✓</span>
                        </template>
                    </button>

                    <button @click="closeModal()" 
                            type="button" 
                            class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider transition cursor-pointer">
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
        /* ── Notch Cutouts (Sobekan Tiket Fisik) ── */
        .luxury-ticket {
            position: relative;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .luxury-ticket .ticket-notch {
            position: absolute;
            top: 50%;
            width: 24px;
            height: 24px;
            background: #0f172a; /* Slate 900 background modal */
            border-radius: 50%;
            transform: translateY(-50%);
            z-index: 20;
        }
        .luxury-ticket .ticket-notch--left {
            left: -12px;
            box-shadow: inset -2px 0 4px rgba(0,0,0,0.2);
        }
        .luxury-ticket .ticket-notch--right {
            right: -12px;
            box-shadow: inset 2px 0 4px rgba(0,0,0,0.2);
        }

        /* ── Tier Themes ── */
        /* Gold Tier (Perfect 50k) */
        .luxury-ticket.ticket--gold {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 35%, #f59e0b 80%, #d97706 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.35);
            border: 1px solid rgba(251, 191, 36, 0.6);
        }

        /* Violet Tier (Near Miss 30k) */
        .luxury-ticket.ticket--violet {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 35%, #a78bfa 75%, #8b5cf6 100%);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.35);
            border: 1px solid rgba(167, 139, 250, 0.6);
        }

        /* Emerald Tier (Good Try 10k) */
        .luxury-ticket.ticket--emerald {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 35%, #34d399 75%, #10b981 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.35);
            border: 1px solid rgba(52, 211, 153, 0.6);
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
