<x-app-layout>
    <x-slot name="title">R_Pay Saya</x-slot>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- ── Kartu Saldo ── --}}
        <div class="kartu-rpay">
            <div class="kartu-rpay-isi">
                <p class="kartu-rpay-label">Saldo R_Pay</p>
                <h1 class="kartu-rpay-saldo">Rp {{ number_format($saldo, 0, ',', '.') }}</h1>
                <p class="kartu-rpay-nama">{{ auth()->user()->name }}</p>
            </div>
            <i class="fa-solid fa-wallet kartu-rpay-ikon"></i>
        </div>

        <div class="info-rpay">
            <i class="fa-solid fa-circle-info"></i>
            <p>
                Saldo R_Pay berasal dari pengembalian dana pesanan. Saldo ini bisa langsung kamu pakai
                untuk belanja di web, atau dicairkan ke rekening bankmu.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

            {{-- ── Form Pencairan ── --}}
            <div class="lg:col-span-2 bg-white border border-border rounded-sm p-6 space-y-4">
                <h2 class="text-xs font-bold text-primary uppercase tracking-wider border-b border-border pb-3">
                    Cairkan ke Rekening Bank
                </h2>

                @php
                    $antre = $pencairan->firstWhere(fn ($p) => in_array($p->status, ['pending', 'processing'], true));
                @endphp

                @if($antre)
                    <div class="antre-kotak">
                        <p class="antre-judul">
                            <i class="fa-solid fa-hourglass-half"></i>
                            Pencairan sedang diproses
                        </p>
                        <p class="antre-nominal">Rp {{ number_format($antre->amount, 0, ',', '.') }}</p>
                        <p class="antre-ket">
                            {{ $antre->reference }} &middot; {{ $antre->bank_name }} {{ $antre->rekening_samar }}
                        </p>
                        @if($antre->estimated_ready_at)
                        <p class="antre-ket">
                            Perkiraan dana sampai
                            <strong>{{ $antre->estimated_ready_at->translatedFormat('l, d F Y') }}</strong>
                        </p>
                        @endif
                        <p class="antre-ket mt-2">
                            Satu pengajuan diselesaikan dulu sebelum kamu bisa mengajukan lagi.
                        </p>
                    </div>
                @elseif($saldo < $minimum)
                    <div class="kurang-kotak">
                        <p>
                            Pencairan minimal <strong>Rp {{ number_format($minimum, 0, ',', '.') }}</strong>.
                            Saldomu saat ini Rp {{ number_format($saldo, 0, ',', '.') }}.
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('rpay.withdraw') }}" class="space-y-3.5">
                        @csrf

                        {{-- Kolom rupiah berpemisah ribuan.
                             Kolom angka bawaan browser menjebak di sini: mengetik
                             "150.000" seperti kebiasaan menulis rupiah dibaca sebagai
                             150, lalu ditolak dengan pesan yang membingungkan. --}}
                        <div x-data="{
                                nominal: {{ (int) old('amount', (int) $saldo) }},
                                batas: {{ (int) $saldo }},

                                get tampil() {
                                    return this.nominal > 0
                                        ? new Intl.NumberFormat('id-ID').format(this.nominal)
                                        : '';
                                },

                                ubah(teks) {
                                    const angka = String(teks).replace(/[^0-9]/g, '');
                                    this.nominal = angka === '' ? 0 : Math.min(parseInt(angka, 10), this.batas);
                                }
                             }">
                            <label class="label-rpay">Nominal Pencairan</label>

                            <div class="kotak-rupiah">
                                <span class="kotak-rupiah-awalan">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off"
                                       :value="tampil"
                                       @input="ubah($event.target.value); $event.target.value = tampil"
                                       placeholder="0"
                                       class="kotak-rupiah-isian">
                            </div>

                            <input type="hidden" name="amount" :value="nominal">

                            <p class="bantu-rpay">
                                Minimal Rp {{ number_format($minimum, 0, ',', '.') }},
                                maksimal Rp {{ number_format($saldo, 0, ',', '.') }}.
                            </p>
                            @error('amount') <p class="galat-rpay">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label-rpay">Bank</label>
                            <select name="bank_name" class="input-rpay">
                                <option value="">-- Pilih Bank --</option>
                                @foreach($daftarBank as $bank)
                                    <option value="{{ $bank }}" @selected(old('bank_name') === $bank)>{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="galat-rpay">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label-rpay">Nomor Rekening</label>
                            <input type="text" name="account_number" value="{{ old('account_number') }}"
                                   inputmode="numeric" placeholder="Contoh: 1234567890" class="input-rpay">
                            @error('account_number') <p class="galat-rpay">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label-rpay">Nama Pemilik Rekening</label>
                            <input type="text" name="account_holder"
                                   value="{{ old('account_holder', auth()->user()->name) }}"
                                   placeholder="Sesuai buku tabungan" class="input-rpay">
                            <p class="bantu-rpay">
                                Harus sama persis dengan nama di rekening, kalau berbeda transfernya bisa ditolak bank.
                            </p>
                            @error('account_holder') <p class="galat-rpay">{{ $message }}</p> @enderror
                        </div>

                        {{-- Keterangan waktu proses, memakai hitungan hari kerja
                             yang sama dengan yang dipakai sistem. --}}
                        <div class="waktu-kotak">
                            <p class="waktu-judul">
                                <i class="fa-solid fa-clock"></i>
                                Proses 1-2 hari kerja
                            </p>
                            <p>
                                Hari Minggu dan tanggal merah tidak dihitung sebagai hari kerja.
                                Kalau diajukan hari ini, perkiraan dana sampai
                                <strong>{{ $perkiraan->translatedFormat('l, d F Y') }}</strong>.
                            </p>
                        </div>

                        <button type="submit" class="tombol-rpay">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                            Ajukan Pencairan
                        </button>
                    </form>
                @endif
            </div>

            {{-- ── Riwayat Mutasi ── --}}
            <div class="lg:col-span-3 bg-white border border-border rounded-sm p-6">
                <h2 class="text-xs font-bold text-primary uppercase tracking-wider border-b border-border pb-3 mb-4">
                    Riwayat Saldo
                </h2>

                <div class="divide-y divide-border">
                    @forelse($mutasi as $baris)
                    <div class="mutasi-baris">
                        <div class="mutasi-ikon {{ $baris->direction === 'credit' ? 'mutasi-masuk' : 'mutasi-keluar' }}">
                            <i class="fa-solid {{ $baris->direction === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                        </div>

                        <div class="mutasi-teks">
                            <p class="mutasi-judul">{{ $baris->description }}</p>
                            <p class="mutasi-waktu">
                                {{ $baris->sumber_label }} &middot;
                                {{ $baris->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>

                        <div class="mutasi-nominal">
                            <p class="{{ $baris->direction === 'credit' ? 'nominal-masuk' : 'nominal-keluar' }}">
                                {{ $baris->direction === 'credit' ? '+' : '−' }}
                                Rp {{ number_format($baris->amount, 0, ',', '.') }}
                            </p>
                            <p class="mutasi-sisa">Sisa Rp {{ number_format($baris->balance_after, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-14 text-center">
                        <i class="fa-solid fa-receipt text-3xl text-gray-200"></i>
                        <p class="text-xs text-gray-400 font-semibold mt-3">Belum ada mutasi R_Pay.</p>
                    </div>
                    @endforelse
                </div>

                @if($mutasi->hasPages())
                    <div class="mt-4 pt-4 border-t border-border">{{ $mutasi->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Gaya ditulis sendiri, tidak mengandalkan kelas Tailwind arbitrary
           yang belum tentu ikut ter-build. */
        .kartu-rpay {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 28px;
            background: linear-gradient(135deg, #1B3A6B 0%, #2d5a9e 100%);
            color: #fff;
        }
        .kartu-rpay-isi { position: relative; z-index: 1; }
        .kartu-rpay-label {
            font-size: 10px; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; opacity: .75;
        }
        .kartu-rpay-saldo { font-size: 34px; font-weight: 900; margin-top: 6px; line-height: 1.1; }
        .kartu-rpay-nama { font-size: 12px; font-weight: 600; opacity: .8; margin-top: 8px; }
        .kartu-rpay-ikon {
            position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
            font-size: 84px; opacity: .12;
        }

        .info-rpay {
            display: flex; gap: 10px; align-items: flex-start;
            background: #eff6ff; border: 1px solid #dbeafe;
            border-radius: 12px; padding: 14px 16px;
            font-size: 12px; color: #1e40af; line-height: 1.6;
        }
        .info-rpay i { margin-top: 2px; }

        .label-rpay {
            display: block; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .06em;
            color: #6b7280; margin-bottom: 5px;
        }
        .input-rpay {
            width: 100%; font-size: 12px; background: #fff;
            border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px;
        }
        .input-rpay:focus { outline: none; border-color: #1B3A6B; box-shadow: 0 0 0 3px rgb(27 58 107 / .1); }
        .bantu-rpay { font-size: 10px; color: #9ca3af; margin-top: 4px; line-height: 1.5; }

        /* Kolom rupiah: awalan "Rp" menyatu dengan kotaknya */
        .kotak-rupiah {
            display: flex; align-items: stretch;
            border: 1px solid #e5e7eb; border-radius: 10px;
            background: #fff; overflow: hidden;
        }
        .kotak-rupiah:focus-within {
            border-color: #1B3A6B;
            box-shadow: 0 0 0 3px rgb(27 58 107 / .1);
        }
        .kotak-rupiah-awalan {
            display: flex; align-items: center;
            padding: 0 12px; background: #f9fafb;
            border-right: 1px solid #e5e7eb;
            font-size: 12px; font-weight: 800; color: #9ca3af;
        }
        .kotak-rupiah-isian {
            flex: 1 1 auto; min-width: 0;
            border: 0; padding: 10px 12px;
            font-size: 13px; font-weight: 700; color: #1f2937;
        }
        .kotak-rupiah-isian:focus { outline: none; box-shadow: none; }
        .galat-rpay { font-size: 10px; color: #dc2626; font-weight: 600; margin-top: 4px; }

        .waktu-kotak {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
            padding: 12px 14px; font-size: 11px; color: #92400e; line-height: 1.6;
        }
        .waktu-judul { font-weight: 800; margin-bottom: 3px; }
        .waktu-judul i { margin-right: 5px; }

        .antre-kotak {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 12px; padding: 16px;
        }
        .antre-judul { font-size: 11px; font-weight: 800; color: #1d4ed8; }
        .antre-judul i { margin-right: 5px; }
        .antre-nominal { font-size: 22px; font-weight: 900; color: #1e3a8a; margin: 4px 0; }
        .antre-ket { font-size: 11px; color: #3b82f6; line-height: 1.6; }

        .kurang-kotak {
            background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 12px;
            padding: 16px; font-size: 12px; color: #6b7280; line-height: 1.6;
        }

        .tombol-rpay {
            width: 100%; background: #1B3A6B; color: #fff;
            font-size: 12px; font-weight: 800; letter-spacing: .05em;
            text-transform: uppercase; padding: 13px; border-radius: 10px;
            transition: background-color 180ms ease;
        }
        .tombol-rpay:hover { background: #2d5a9e; }
        .tombol-rpay i { margin-right: 6px; }

        .mutasi-baris { display: flex; align-items: center; gap: 13px; padding: 14px 0; }
        .mutasi-ikon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 12px;
        }
        .mutasi-masuk { background: #ecfdf5; color: #047857; }
        .mutasi-keluar { background: #fef2f2; color: #b91c1c; }
        .mutasi-teks { flex: 1 1 auto; min-width: 0; }
        .mutasi-judul {
            font-size: 12px; font-weight: 700; color: #374151;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .mutasi-waktu { font-size: 10px; color: #9ca3af; margin-top: 2px; }
        .mutasi-nominal { text-align: right; flex-shrink: 0; }
        .nominal-masuk { font-size: 12px; font-weight: 900; color: #047857; }
        .nominal-keluar { font-size: 12px; font-weight: 900; color: #b91c1c; }
        .mutasi-sisa { font-size: 10px; color: #9ca3af; margin-top: 2px; }
    </style>
    @endpush
</x-app-layout>
