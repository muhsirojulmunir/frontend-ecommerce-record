<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}Record - LANGKAHPENUHGAYA</title>

        {{-- Font Inter dari Google --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        {{-- Ikon Font Awesome --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        {{-- CSS & JS dikompilasi oleh Vite --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-text bg-bg-secondary">
        <div class="min-h-screen flex flex-col">
            {{-- Bar atas bermerek, senada dengan halaman utama --}}
            <div class="bg-primary text-white text-xs py-2 px-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <span class="font-medium tracking-wide">Record - LANGKAHPENUHGAYA</span>
                    <a href="{{ route('home') }}" class="hover:text-gray-200 transition font-medium uppercase">
                        <i class="fas fa-arrow-left-long mr-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>

            {{-- Konten form (login/register/dll) --}}
            <div class="flex-1 flex flex-col items-center justify-center px-4 py-12">
                <a href="{{ route('home') }}" class="mb-8">
                    <span class="text-3xl font-black tracking-tight uppercase shimmer-logo-primary">
                        Record
                    </span>
                </a>

                <div class="w-full sm:max-w-md bg-white border border-border rounded-sm shadow-sm p-8 sm:p-10">
                    {{ $slot }}
                </div>

                <p class="mt-8 text-xs text-text-light text-center max-w-xs">
                    Dengan melanjutkan, Anda menyetujui
                    <a href="#" class="font-semibold text-text hover:text-primary underline underline-offset-2">Syarat & Ketentuan</a>
                    serta
                    <a href="#" class="font-semibold text-text hover:text-primary underline underline-offset-2">Kebijakan Privasi</a>
                    Record.
                </p>
            </div>
        </div>
    </body>
</html>
