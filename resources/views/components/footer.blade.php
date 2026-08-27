<footer id="footer" class="bg-primary text-white pt-16 pb-8 border-t-4 border-accent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            {{-- Kolom 1: Alamat kantor pusat --}}
            <div>
                <h3 class="text-lg font-bold tracking-wider mb-6 text-white border-b-2 border-accent pb-2 inline-block">
                    JAYA MANDIRI</h3>
                <div class="space-y-4 text-sm text-gray-300">
                    <div>
                        <p class="font-semibold text-white">Head Office</p>
                        <p class="mt-1 text-xs">Jl. Kyai tambak deres No.30,</p>
                        <p class="text-xs">Kedung Cowek, Kec. Bulak, Surabaya,</p>
                        <p class="text-xs">Jawa Timur, Indonesia</p>
                    </div>
                    <div>
                        <p class="text-xs"><span class="font-semibold text-white">Phone:</span> (031) 869 1888</p>
                        <p class="text-xs"><span class="font-semibold text-white">WA:</span> +62 813-2306-5554</p>
                        <p class="text-[10px] text-gray-400 mt-1">(Office Hour)</p>
                    </div>
                </div>
            </div>

            {{-- Kolom 2: Tautan perusahaan --}}
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider mb-6 text-white border-b border-white/20 pb-2">
                    Company</h3>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="{{ route('about') }}" class="hover:text-white hover:underline transition">About us</a>
                    </li>
                    <li><a href="{{ route('kontak') }}" class="hover:text-white hover:underline transition">Contact us</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white hover:underline transition">FAQ</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Layanan pelanggan --}}
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider mb-6 text-white border-b border-white/20 pb-2">
                    Customer Care</h3>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-white hover:underline transition">My
                            Account</a></li>
                    <li><a href="{{ route('tracking') }}" class="hover:text-white hover:underline transition">Tracking Order</a></li>
                    <li><a href="{{ route('pengiriman-retur') }}" class="hover:text-white hover:underline transition">Pengiriman &amp; Retur</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Logo brand dan metode pembayaran --}}
            <div>
                <div class="mb-6">
                    {{-- Varian gelap: cahayanya keputihan agar terbaca di latar gelap --}}
                    <x-logo-brand ukuran="kecil" varian="gelap" class="-ml-1" />
                    <p class="text-xs text-gray-400 italic mt-2">LANGKAHPENUHGAYA</p>
                </div>

                <div class="flex gap-3">
                    <a href="#"
                        class="bg-white/10 hover:bg-white/20 transition rounded-full h-8 w-8 flex items-center justify-center"><i
                            class="fab fa-facebook-f text-sm"></i></a>
                    <a href="#"
                        class="bg-white/10 hover:bg-white/20 transition rounded-full h-8 w-8 flex items-center justify-center"><i
                            class="fab fa-instagram text-sm"></i></a>
                    <a href="#"
                        class="bg-white/10 hover:bg-white/20 transition rounded-full h-8 w-8 flex items-center justify-center"><i
                            class="fab fa-youtube text-sm"></i></a>
                </div>
            </div>
        </div>

        {{-- Bar bawah: kebijakan dan hak cipta --}}
        <div
            class="border-t border-white/10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-gray-400">
            <div class="flex gap-4">
                <a href="{{ route('pengiriman-retur') }}" class="hover:text-white transition">SHIPPING &amp; RETURNS</a>
                <a href="{{ route('kebijakan-privasi') }}" class="hover:text-white transition">PRIVACY POLICY</a>
                <a href="{{ route('syarat-ketentuan') }}" class="hover:text-white transition">PERSYARATAN &amp; KETENTUAN</a>
            </div>
            <p>Copyright 2026 Â© PT JAYA MANDIRI</p>
        </div>
    </div>
</footer>