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
                                        @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-sm text-[10px] font-black uppercase bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                                <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span> Belum Dibayar
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-sm text-[9px] font-bold uppercase
                                                @if($order->status === 'completed') bg-emerald-50 text-emerald-800 border border-emerald-200
                                                @elseif($order->status === 'pending') bg-amber-50 text-amber-800 border border-amber-200
                                                @elseif($order->status === 'shipped') bg-sky-50 text-sky-800 border border-sky-200
                                                @elseif($order->status === 'processing') bg-blue-50 text-blue-800 border border-blue-200
                                                @elseif($order->status === 'cancelled') bg-gray-50 text-gray-500 border border-gray-200
                                                @else bg-gray-50 text-gray-600 border border-gray-200
                                                @endif">
                                                {{ $order->status_label ?? ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                                            <a href="{{ route('checkout.payment', $order->order_number) }}"
                                                style="background-color: #dc2626; color: #ffffff;"
                                                class="inline-flex items-center gap-1.5 text-[11px] font-bold hover:bg-rose-700 px-3 py-1.5 rounded-sm uppercase tracking-wider transition shadow-sm">
                                                <i class="fa-solid fa-credit-card text-[9px]"></i> Bayar Sekarang
                                            </a>
                                        @else
                                            <a href="{{ route('orders.show', $order->order_number) }}"
                                                class="text-xs font-bold text-accent hover:text-accent-dark transition uppercase">
                                                Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
                                <!-- ══ PENGATURAN AKUN (Profil, Kata Sandi, Hapus Akun) ══ -->
        <div id="pengaturan-akun" class="mt-14 mb-10">
            {{-- Header Section Title --}}
            <div class="mb-6">
                <h2 class="text-sm font-black uppercase tracking-wider text-primary flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-accent"></i> Pengaturan Akun
                </h2>
                <p class="text-xs text-text-light mt-1">Kelola data profil pribadi dan keamanan akses akun Anda</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Kartu 1: Informasi Profil -->
                <div class="bg-white border border-gray-200 rounded-sm p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-sm bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Profil</h3>
                                <p class="text-[11px] text-gray-500 mt-0.5">Perbarui nama lengkap dan alamat email akun Anda</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            <div>
                                <label for="profile_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Nama Lengkap</label>
                                <input id="profile_name" name="name" type="text"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-primary focus:border-primary transition"
                                    value="{{ old('name', Auth::user()->name) }}" required autocomplete="name"
                                    placeholder="Masukkan nama lengkap Anda" />
                                <x-input-error class="mt-1" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <label for="profile_email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Alamat Email</label>
                                <input id="profile_email" name="email" type="email"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-primary focus:border-primary transition"
                                    value="{{ old('email', Auth::user()->email) }}" required autocomplete="username"
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
                <div class="bg-white border border-gray-200 rounded-sm p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-sm bg-accent/10 text-accent flex items-center justify-center font-bold text-sm shrink-0">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-primary">Keamanan Kata Sandi</h3>
                                <p class="text-[11px] text-gray-500 mt-0.5">Gunakan kata sandi yang kuat agar akun tetap terlindungi</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                            @csrf
                            @method('put')

                            <div>
                                <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Kata Sandi Saat Ini</label>
                                <input id="current_password" name="current_password" type="password"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-primary focus:border-primary transition"
                                    autocomplete="current-password" placeholder="••••••••" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                            </div>

                            <div>
                                <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Kata Sandi Baru</label>
                                <input id="new_password" name="password" type="password"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-primary focus:border-primary transition"
                                    autocomplete="new-password" placeholder="Minimal 8 karakter" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                                <input id="new_password_confirmation" name="password_confirmation" type="password"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-primary focus:border-primary transition"
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

            <!-- ── Kartu 3: Hapus Akun (Dengan Verifikasi Password & Email OTP) ── -->
            <div class="mt-6 bg-white border border-gray-200 rounded-sm p-6 sm:p-7 shadow-xs"
                 x-data="{
                     modalOpen: false,
                     step: 1,
                     password: '',
                     otpCode: '',
                     loading: false,
                     errorMessage: '',
                     successMessage: '',
                     bukaModal() {
                         this.modalOpen = true;
                         this.step = 1;
                         this.password = '';
                         this.otpCode = '';
                         this.errorMessage = '';
                         this.successMessage = '';
                     },
                     tutupModal() {
                         this.modalOpen = false;
                         this.errorMessage = '';
                         this.successMessage = '';
                     },
                     async kirimPermintaanOTP() {
                         if (!this.password) {
                             this.errorMessage = 'Silakan masukkan kata sandi akun Anda.';
                             return;
                         }
                         this.loading = true;
                         this.errorMessage = '';
                         try {
                             const res = await fetch('{{ route('profile.delete-request') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({ password: this.password })
                             });
                             const data = await res.json();
                             if (!res.ok) {
                                 this.errorMessage = data.message || 'Kata sandi yang Anda masukkan salah.';
                                 this.loading = false;
                                 return;
                             }
                             this.successMessage = data.message;
                             this.step = 2;
                         } catch (e) {
                             this.errorMessage = 'Terjadi kendala jaringan. Silakan coba lagi.';
                         } finally {
                             this.loading = false;
                         }
                     },
                     async konfirmasiHapusFinal() {
                         if (!this.otpCode || this.otpCode.length < 6) {
                             this.errorMessage = 'Masukkan 6 digit kode verifikasi yang ada di email Anda.';
                             return;
                         }
                         this.loading = true;
                         this.errorMessage = '';
                         try {
                             const res = await fetch('{{ route('profile.destroy') }}', {
                                 method: 'DELETE',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({ code: this.otpCode })
                             });
                             const data = await res.json();
                             if (!res.ok) {
                                 this.errorMessage = data.message || 'Kode verifikasi salah atau kedaluwarsa.';
                                 this.loading = false;
                                 return;
                             }
                             window.location.href = data.redirect || '/';
                         } catch (e) {
                             this.errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                             this.loading = false;
                         }
                     }
                 }">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-sm bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800">Hapus Akun</h3>
                            <p class="text-xs text-gray-500 mt-0.5 max-w-xl leading-relaxed">
                                Setelah akun dihapus, Anda tidak dapat login kembali. Riwayat transaksi belanja Anda tetap tersimpan di sistem toko untuk keperluan pembukuan.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="bukaModal()"
                        style="background-color: #ffffff; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700;"
                        class="hover:bg-rose-50 text-xs px-5 py-2.5 rounded-sm uppercase tracking-wider transition shrink-0 shadow-xs flex items-center gap-2">
                        <i class="fa-solid fa-trash-can"></i> Hapus Akun Saya
                    </button>
                </div>

                <!-- ── Modal Konfirmasi Hapus Akun 2-Langkah ── -->
                <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div @click.away="tutupModal()" class="bg-white rounded-sm shadow-2xl max-w-md w-full p-6 sm:p-7 text-left space-y-5 border border-gray-200">
                        
                        {{-- Header Modal --}}
                        <div class="flex items-center gap-3.5 border-b border-gray-100 pb-4">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wide">Konfirmasi Hapus Akun</h3>
                                <p class="text-xs text-gray-500">Tindakan ini memerlukan verifikasi ganda</p>
                            </div>
                        </div>

                        {{-- Alert Error jika ada --}}
                        <div x-show="errorMessage" x-cloak class="p-3 rounded-sm bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        {{-- ── STEP 1: Masukkan Kata Sandi ── --}}
                        <div x-show="step === 1" class="space-y-4">
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Untuk keamanan, silakan masukkan <strong>Kata Sandi Akun</strong> Anda terlebih dahulu. Kami akan mengirimkan <strong>Kode Verifikasi</strong> ke email Anda.
                            </p>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi Akun</label>
                                <input type="password" x-model="password" @keydown.enter.prevent="kirimPermintaanOTP()"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-sm text-gray-800 focus:ring-1 focus:ring-rose-500 focus:border-rose-500"
                                    placeholder="Masukkan kata sandi akun Anda" />
                            </div>

                            <div class="flex justify-end items-center gap-3 pt-3 border-t border-gray-100">
                                <button type="button" @click="tutupModal()"
                                    style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;"
                                    class="hover:bg-gray-200 text-xs font-bold px-5 py-2.5 rounded-sm transition uppercase">
                                    Batal
                                </button>
                                <button type="button" @click="kirimPermintaanOTP()" :disabled="loading"
                                    style="background-color: #1B3A6B; color: #ffffff;"
                                    class="hover:bg-primary-light text-xs font-bold px-5 py-2.5 rounded-sm transition uppercase shadow-sm flex items-center gap-2">
                                    <span x-show="!loading"><i class="fa-solid fa-envelope"></i> Lanjut & Kirim Kode Email</span>
                                    <span x-show="loading" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
                                </button>
                            </div>
                        </div>

                        {{-- ── STEP 2: Masukkan Kode Verifikasi Email ── --}}
                        <div x-show="step === 2" x-cloak class="space-y-4">
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-sm text-emerald-800 text-xs">
                                <p class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i> Kode Berhasil Dikirim!</p>
                                <p class="text-[11px] text-emerald-700 mt-1" x-text="successMessage"></p>
                            </div>

                            <p class="text-xs text-gray-600 leading-relaxed">
                                Masukkan <strong>6 Digit Kode Verifikasi</strong> yang telah kami kirimkan ke email Anda untuk menyelesaikan penghapusan akun:
                            </p>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kode Verifikasi (6 Digit)</label>
                                <input type="text" x-model="otpCode" maxlength="6" @keydown.enter.prevent="konfirmasiHapusFinal()"
                                    class="w-full bg-white border border-gray-300 rounded-sm px-4 py-2.5 text-center font-mono font-bold text-lg text-primary tracking-widest focus:ring-1 focus:ring-rose-500 focus:border-rose-500"
                                    placeholder="000000" />
                            </div>

                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                <button type="button" @click="kirimPermintaanOTP()" :disabled="loading"
                                    class="text-xs text-primary hover:underline font-bold">
                                    Kirim Ulang Kode
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="tutupModal()"
                                        style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;"
                                        class="hover:bg-gray-200 text-xs font-bold px-4 py-2.5 rounded-sm transition uppercase">
                                        Batal
                                    </button>
                                    <button type="button" @click="konfirmasiHapusFinal()" :disabled="loading"
                                        style="background-color: #dc2626; color: #ffffff; border: 1px solid #b91c1c;"
                                        class="hover:bg-rose-700 text-xs font-bold px-5 py-2.5 rounded-sm transition uppercase shadow-sm flex items-center gap-2">
                                        <span x-show="!loading"><i class="fa-solid fa-trash-can"></i> Ya, Hapus Akun Permanen</span>
                                        <span x-show="loading" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Menghapus...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@push('styles')
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
