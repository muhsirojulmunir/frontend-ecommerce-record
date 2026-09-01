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
                    @php
                        $unpaidCount = Auth::user()->unpaidOrdersCount();
                    @endphp
                    <a href="{{ route('dashboard') }}" class="bar-atas-tautan hover:text-gray-200 transition font-medium flex items-center gap-1.5">
                        <span>Akun Saya</span>
                        @if($unpaidCount > 0)
                            <span style="background-color: #dc2626; color: #ffffff; border-radius: 9999px; min-width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900; padding: 0 5px; line-height: 1; margin-left: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                {{ $unpaidCount }}
                            </span>
                        @endif
                    </a>
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