<div class="bg-primary text-white text-xs py-2 px-4">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="font-medium tracking-wide">
            Record - LANGKAHPENUHGAYA
        </div>
        <div class="flex items-center gap-4">
            @auth
                <span class="text-gray-300">Halo, {{ Auth::user()->name }}</span>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="bar-atas-tautan hover:text-gray-200 transition font-medium">Dashboard Admin</a>
                @else
                    <a href="{{ route('dashboard') }}" class="bar-atas-tautan hover:text-gray-200 transition font-medium">Akun Saya</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bar-atas-tautan hover:text-gray-200 transition font-medium">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="bar-atas-tautan hover:text-gray-200 transition font-medium uppercase">Login</a>
                <a href="{{ route('register') }}" class="bar-atas-tautan hover:text-gray-200 transition font-medium uppercase border-l border-white/30 pl-4">Daftar</a>
            @endauth
            
            <div class="hidden md:flex items-center gap-3 border-l border-white/30 pl-4">
                <a href="https://www.instagram.com/recordshoesofficial/" target="_blank" rel="noopener noreferrer" class="hover:text-gray-300 transition" aria-label="Instagram RECORD"><i class="fab fa-instagram text-sm"></i></a>
                <a href="https://www.tiktok.com/@recordshoesofficial_id" target="_blank" rel="noopener noreferrer" class="hover:text-gray-300 transition" aria-label="TikTok RECORD"><i class="fab fa-tiktok text-sm"></i></a>
            </div>
        </div>
    </div>
</div>