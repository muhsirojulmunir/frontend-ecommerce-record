<x-app-layout>
    <x-slot name="title">Akun Saya</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header Banner -->
        <div class="bg-primary text-white p-8 rounded-sm shadow-sm mb-8 border-b-4 border-accent flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wider">Selamat Datang, {{ Auth::user()->name }}!</h1>
                <p class="text-xs text-gray-300 mt-1 uppercase tracking-wide">Akun belanja Anda di Record — LANGKAHPENUHGAYA</p>
            </div>
            <a href="{{ route('products.index') }}"
                class="bg-accent hover:bg-accent-light text-white text-xs font-bold px-6 py-3 rounded-sm uppercase tracking-wide transition shadow-md flex-shrink-0">
                Belanja Sekarang
            </a>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-border p-5 rounded-sm shadow-sm text-center">
                <p class="text-3xl font-black text-primary">{{ $totalOrders }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-text-light mt-1">Total Pesanan</p>
            </div>
            <div class="bg-white border border-border p-5 rounded-sm shadow-sm text-center">
                <p class="text-2xl font-black text-accent">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-text-light mt-1">Total Belanja</p>
            </div>
            <div class="bg-white border border-border p-5 rounded-sm shadow-sm text-center">
                <p class="text-3xl font-black text-primary">{{ $pendingOrders }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-text-light mt-1">Pesanan Menunggu</p>
            </div>
        </div>

        <!-- ══ R_Pay & Kode Referal ══ -->
        <div class="baris-dompet">

            {{-- Saldo R_Pay --}}
            <div class="kartu-dompet">
                <div class="kartu-dompet-isi">
                    <p class="kartu-dompet-label">Saldo R_Pay</p>
                    <h2 class="kartu-dompet-saldo">Rp {{ number_format($saldoRpay, 0, ',', '.') }}</h2>
                    <p class="kartu-dompet-ket">Bisa dipakai belanja atau dicairkan ke bank</p>
                    <a href="{{ route('rpay.index') }}" class="kartu-dompet-tautan">
                        Buka R_Pay <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
                <i class="fa-solid fa-wallet kartu-dompet-ikon"></i>
            </div>

            {{-- Kode referal --}}
            <div class="kartu-referal-dash">
                <p class="kartu-referal-label">Kode Referal Kamu</p>

                @if($referalAktif)
                    <div x-data="{ tersalin: false }" class="kartu-referal-kotak">
                        <span class="kartu-referal-kode">{{ $kodeReferal }}</span>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $kodeReferal }}');
                                        tersalin = true; setTimeout(() => tersalin = false, 2000)"
                                class="kartu-referal-salin">
                            <span x-show="!tersalin"><i class="fa-solid fa-copy"></i> Salin</span>
                            <span x-show="tersalin" x-cloak><i class="fa-solid fa-check"></i> Tersalin</span>
                        </button>
                    </div>

                    <p class="kartu-referal-ket">
                        Bagikan ke teman. Mereka hemat
                        {{ (int) config('referal.persen_diskon', 3) }}%, kamu dapat komisi
                        {{ (int) config('referal.persen_komisi', 3) }}% ke saldo R_Pay.
                    </p>

                    <div class="kartu-referal-angka">
                        <div>
                            <p class="kartu-referal-angka-nilai">{{ number_format($dipakaiOrang) }}</p>
                            <p class="kartu-referal-angka-label">Kali dipakai</p>
                        </div>
                        <div>
                            <p class="kartu-referal-angka-nilai">Rp {{ number_format($komisiDiterima, 0, ',', '.') }}</p>
                            <p class="kartu-referal-angka-label">Komisi diterima</p>
                        </div>
                    </div>
                @else
                    {{-- Kode belum terbit, atau hangus karena pesanannya dibatalkan. --}}
                    <div class="kartu-referal-belum">
                        <i class="fa-solid fa-lock"></i>
                        <div>
                            <p class="kartu-referal-belum-judul">Belum tersedia</p>
                            <p class="kartu-referal-belum-ket">
                                @if(filled($kodeReferal))
                                    Kode referalmu sedang tidak berlaku karena pesananmu dibatalkan.
                                    Selesaikan satu pesanan lagi untuk mengaktifkannya kembali.
                                @else
                                    Checkout dulu dan selesaikan pembayaran, kode referalmu akan
                                    terbit otomatis setelah itu.
                                @endif
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('products.index') }}" class="kartu-referal-ajakan">
                        Mulai Belanja <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Quick Access Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <!-- Riwayat Pesanan -->
            <a href="{{ route('orders.index') }}" class="bg-white border border-border p-6 rounded-sm shadow-sm hover:shadow-md hover:border-primary/30 transition group flex flex-col justify-between h-44">
                <div>
                    <div class="flex items-center gap-3 text-primary mb-3">
                        <i class="fas fa-shopping-bag text-xl"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider">Riwayat Pesanan</h3>
                    </div>
                    <p class="text-xs text-text-light leading-relaxed">Lihat transaksi, lacak status pengiriman, dan nomor resi pesanan sepatu Anda.</p>
                </div>
                <div class="pt-4 border-t border-border">
                    <span class="text-xs font-bold text-accent group-hover:text-accent-dark transition uppercase flex items-center gap-1">
                        Lihat Pesanan <i class="fas fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- Detail Akun -->
            <a href="{{ route('profile.edit') }}" class="bg-white border border-border p-6 rounded-sm shadow-sm hover:shadow-md hover:border-primary/30 transition group flex flex-col justify-between h-44">
                <div>
                    <div class="flex items-center gap-3 text-primary mb-3">
                        <i class="fas fa-user-cog text-xl"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider">Detail Akun</h3>
                    </div>
                    <p class="text-xs text-text-light leading-relaxed">Ubah nama, email, nomor telepon, dan kata sandi akun Anda.</p>
                </div>
                <div class="pt-4 border-t border-border">
                    <span class="text-xs font-bold text-accent group-hover:text-accent-dark transition uppercase flex items-center gap-1">
                        Ubah Profil <i class="fas fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>

            <!-- Belanja Lagi -->
            <a href="{{ route('products.index') }}" class="bg-white border border-border p-6 rounded-sm shadow-sm hover:shadow-md hover:border-primary/30 transition group flex flex-col justify-between h-44">
                <div>
                    <div class="flex items-center gap-3 text-primary mb-3">
                        <i class="fas fa-shoe-prints text-xl"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider">Katalog Produk</h3>
                    </div>
                    <p class="text-xs text-text-light leading-relaxed">Jelajahi koleksi terbaru sepatu Record. Temukan yang paling pas untuk Anda.</p>
                </div>
                <div class="pt-4 border-t border-border">
                    <span class="text-xs font-bold text-accent group-hover:text-accent-dark transition uppercase flex items-center gap-1">
                        Lihat Katalog <i class="fas fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- Recent Orders Table -->
        @if($recentOrders->count() > 0)
            <div class="bg-white border border-border rounded-sm shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border flex justify-between items-center">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary">5 Pesanan Terakhir</h2>
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-accent hover:text-accent-dark uppercase">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-xs">
                        <thead class="bg-gray-50/50 text-[10px] font-bold uppercase text-primary tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">No. Pesanan</th>
                                <th class="px-6 py-3 text-left">Tanggal</th>
                                <th class="px-6 py-3 text-left">Total</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-primary">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 text-text-light">{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 font-bold text-text">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase
                                            @if($order->status === 'completed') bg-emerald-50 text-emerald-800 border border-emerald-200
                                            @elseif($order->status === 'pending') bg-amber-50 text-amber-800 border border-amber-200
                                            @elseif($order->status === 'shipped') bg-sky-50 text-sky-800 border border-sky-200
                                            @else bg-gray-50 text-gray-600 border border-gray-200
                                            @endif">
                                            {{ $order->status_label ?? ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('orders.show', $order->order_number) }}"
                                            class="text-xs font-bold text-accent hover:text-accent-dark transition uppercase">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
                <!-- ══ PENGATURAN AKUN (Profil, Kata Sandi, Hapus Akun) ══ -->
        <div id="pengaturan-akun" class="mt-12 bg-white border border-border rounded-sm shadow-sm overflow-hidden">
            {{-- Header Section --}}
            <div class="px-6 py-5 border-b border-border bg-gray-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-primary flex items-center gap-2">
                        <i class="fa-solid fa-user-gear text-accent"></i> Pengaturan Akun
                    </h2>
                    <p class="text-xs text-text-light mt-0.5">Kelola profil pribadi dan keamanan akses akun Anda</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Kartu 1: Informasi Profil -->
                    <div class="border border-border/80 rounded-sm p-6 bg-white shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-5 pb-3 border-b border-border/60">
                                <div class="w-9 h-9 rounded-sm bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Profil</h3>
                                    <p class="text-[11px] text-text-light">Perbarui nama dan alamat email akun Anda</p>
                                </div>
                            </div>

                            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                                @csrf
                                @method('patch')

                                <div>
                                    <x-input-label for="profile_name" value="Nama Lengkap" />
                                    <x-text-input id="profile_name" name="name" type="text"
                                        :value="old('name', Auth::user()->name)" required autocomplete="name"
                                        placeholder="Masukkan nama lengkap Anda" />
                                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                                </div>

                                <div>
                                    <x-input-label for="profile_email" value="Alamat Email" />
                                    <x-text-input id="profile_email" name="email" type="email"
                                        :value="old('email', Auth::user()->email)" required autocomplete="username"
                                        placeholder="nama@email.com" />
                                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                                </div>

                                <div class="pt-3 flex items-center gap-3">
                                    <button type="submit" class="bg-primary hover:bg-primary-light text-white text-xs font-bold px-6 py-2.5 rounded-sm uppercase tracking-wider transition shadow-sm flex items-center gap-2">
                                        <i class="fa-solid fa-floppy-disk"></i> Simpan Profil
                                    </button>

                                    @if (session('status') === 'profile-updated')
                                        <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                            class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-check"></i> Tersimpan!
                                        </span>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Kartu 2: Keamanan Kata Sandi -->
                    <div class="border border-border/80 rounded-sm p-6 bg-white shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-5 pb-3 border-b border-border/60">
                                <div class="w-9 h-9 rounded-sm bg-accent/10 text-accent flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary">Keamanan Kata Sandi</h3>
                                    <p class="text-[11px] text-text-light">Gunakan kata sandi yang kuat agar akun tetap terlindungi</p>
                                </div>
                            </div>

                            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                                @csrf
                                @method('put')

                                <div>
                                    <x-input-label for="current_password" value="Kata Sandi Saat Ini" />
                                    <x-text-input id="current_password" name="current_password" type="password"
                                        autocomplete="current-password" placeholder="••••••••" />
                                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="new_password" value="Kata Sandi Baru" />
                                    <x-text-input id="new_password" name="password" type="password"
                                        autocomplete="new-password" placeholder="Minimal 8 karakter" />
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="new_password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                                    <x-text-input id="new_password_confirmation" name="password_confirmation" type="password"
                                        autocomplete="new-password" placeholder="Ulangi kata sandi baru" />
                                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                                </div>

                                <div class="pt-3 flex items-center gap-3">
                                    <button type="submit" class="bg-primary hover:bg-primary-light text-white text-xs font-bold px-6 py-2.5 rounded-sm uppercase tracking-wider transition shadow-sm flex items-center gap-2">
                                        <i class="fa-solid fa-key"></i> Perbarui Kata Sandi
                                    </button>

                                    @if (session('status') === 'password-updated')
                                        <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                            class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-check"></i> Kata sandi diperbarui!
                                        </span>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kartu 3: Danger Zone / Hapus Akun -->
                <div class="mt-8 pt-6 border-t border-border/80" x-data="{ confirmingUserDeletion: false }">
                    <div class="bg-gray-50 border border-gray-200/80 rounded-sm p-5 sm:p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-sm bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800">Hapus Akun</h3>
                                <p class="text-xs text-text-light mt-0.5 max-w-xl leading-relaxed">
                                    Setelah akun dihapus, Anda tidak dapat login kembali. Riwayat transaksi belanja Anda tetap tersimpan di sistem toko untuk keperluan pembukuan.
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="confirmingUserDeletion = true"
                            class="bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-rose-300 text-xs font-bold px-5 py-2.5 rounded-sm uppercase tracking-wider transition shrink-0 shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-trash-can"></i> Hapus Akun Saya
                        </button>
                    </div>

                    <!-- Modal Konfirmasi Hapus Akun -->
                    <div x-show="confirmingUserDeletion" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                        <div @click.away="confirmingUserDeletion = false" class="bg-white rounded-sm shadow-xl max-w-md w-full p-6 text-left space-y-4 border border-border">
                            <div class="flex items-center gap-3 text-rose-600">
                                <div class="w-10 h-10 rounded-sm bg-rose-100 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wide">Konfirmasi Hapus Akun</h3>
                                    <p class="text-xs text-text-light">Tindakan ini tidak dapat dibatalkan</p>
                                </div>
                            </div>

                            <p class="text-xs text-text leading-relaxed">
                                Apakah Anda yakin ingin menghapus akun ini? Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan akun.
                            </p>

                            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                                @csrf
                                @method('delete')

                                <div>
                                    <x-input-label for="delete_account_password" value="Kata Sandi" />
                                    <x-text-input id="delete_account_password" name="password" type="password" required
                                        placeholder="Masukkan kata sandi akun Anda" />
                                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="confirmingUserDeletion = false"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2.5 rounded-sm transition uppercase">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-5 py-2.5 rounded-sm transition uppercase shadow-sm flex items-center gap-2">
                                        <i class="fa-solid fa-trash-can"></i> Ya, Hapus Akun
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>@push('styles')
    <style>
        /* Gaya ditulis sendiri, tidak mengandalkan kelas Tailwind yang belum tentu ikut ter-build. */
        .baris-dompet {
            display: grid; gap: 20px; margin-bottom: 32px;
        }
        @media (min-width: 900px) { .baris-dompet { grid-template-columns: 1fr 1.15fr; } }

        /* ── Kartu saldo R_Pay ── */
        .kartu-dompet {
            position: relative; overflow: hidden;
            border-radius: 16px; padding: 26px;
            background: linear-gradient(135deg, #1B3A6B 0%, #2d5a9e 100%);
            color: #fff;
        }
        .kartu-dompet-isi { position: relative; z-index: 1; }
        .kartu-dompet-label {
            font-size: 10px; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; opacity: .75;
        }
        .kartu-dompet-saldo { font-size: 32px; font-weight: 900; margin-top: 6px; line-height: 1.1; }
        .kartu-dompet-ket { font-size: 11px; opacity: .8; margin-top: 6px; }
        .kartu-dompet-tautan {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 16px;
            background: rgb(255 255 255 / .16); border: 1px solid rgb(255 255 255 / .28);
            padding: 8px 14px; border-radius: 8px;
            font-size: 11px; font-weight: 800; color: #fff;
            transition: background-color 160ms ease;
        }
        .kartu-dompet-tautan:hover { background: rgb(255 255 255 / .26); }
        .kartu-dompet-ikon {
            position: absolute; right: 22px; top: 50%; transform: translateY(-50%);
            font-size: 84px; opacity: .12;
        }

        /* ── Kartu kode referal ── */
        .kartu-referal-dash {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 16px; padding: 24px;
        }
        .kartu-referal-label {
            font-size: 10px; font-weight: 800; letter-spacing: .1em;
            text-transform: uppercase; color: #9ca3af;
        }

        .kartu-referal-kotak {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-top: 10px; padding: 14px 16px;
            background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px;
        }
        .kartu-referal-kode {
            flex: 1 1 auto; min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 17px; font-weight: 900; letter-spacing: .04em;
            color: #1B3A6B; word-break: break-all;
        }
        .kartu-referal-salin {
            flex-shrink: 0; background: #1B3A6B; color: #fff;
            padding: 8px 14px; border-radius: 8px;
            font-size: 11px; font-weight: 800;
            transition: background-color 160ms ease;
        }
        .kartu-referal-salin:hover { background: #2d5a9e; }

        .kartu-referal-ket {
            font-size: 11px; color: #6b7280; line-height: 1.6; margin-top: 11px;
        }

        .kartu-referal-angka {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
            margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6;
        }
        .kartu-referal-angka-nilai { font-size: 17px; font-weight: 900; color: #1f2937; }
        .kartu-referal-angka-label {
            font-size: 9.5px; font-weight: 800; letter-spacing: .06em;
            text-transform: uppercase; color: #9ca3af; margin-top: 2px;
        }

        .kartu-referal-belum {
            display: flex; gap: 13px; align-items: flex-start;
            margin-top: 10px; padding: 16px;
            background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 12px;
        }
        .kartu-referal-belum > i { color: #9ca3af; font-size: 15px; margin-top: 2px; }
        .kartu-referal-belum-judul { font-size: 12.5px; font-weight: 800; color: #4b5563; }
        .kartu-referal-belum-ket {
            font-size: 11px; color: #6b7280; line-height: 1.65; margin-top: 3px;
        }
        .kartu-referal-ajakan {
            display: inline-flex; align-items: center; gap: 7px; margin-top: 14px;
            background: #1B3A6B; color: #fff;
            padding: 10px 18px; border-radius: 8px;
            font-size: 11px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .04em;
            transition: background-color 160ms ease;
        }
        .kartu-referal-ajakan:hover { background: #2d5a9e; }

        [x-cloak] { display: none !important; }
    </style>
    @endpush
</x-app-layout>
