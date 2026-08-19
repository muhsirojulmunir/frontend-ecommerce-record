@if (file_exists(public_path('images/Logo_Brand.png')) || file_exists(public_path('images/Logo_Brand.png')) || file_exists(public_path('images/Logo_Brand.svg')) || file_exists(public_path('images/Logo_Brand.webp')))
    <img src="{{ asset('images/' . (file_exists(public_path('images/Logo_Brand.png')) ? 'Logo_Brand.png' : (file_exists(public_path('images/Logo_Brand.png')) ? 'Logo_Brand.png' : (file_exists(public_path('images/Logo_Brand.svg')) ? 'Logo_Brand.svg' : 'Logo_Brand.webp')))) }}" 
         alt="Logo Brand" 
         class="h-10 sm:h-12 w-auto object-contain shimmer-logo-img">
@else
    <span class="text-xl font-black tracking-tight uppercase shimmer-logo-primary">
        Record
    </span>
@endif
