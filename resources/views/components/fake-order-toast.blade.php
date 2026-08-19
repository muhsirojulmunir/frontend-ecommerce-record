@props(['products' => []])

<div x-data="fakeOrderToast({{ json_encode($products) }})"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-500 transform"
     x-transition:enter-start="-translate-y-12 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-400 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
     class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full pointer-events-auto">
    
    <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-2xl p-4 shadow-2xl flex items-center gap-3.5 relative overflow-hidden">
        {{-- Progress Bar indicator --}}
        <div class="absolute bottom-0 left-0 h-1 bg-accent transition-all duration-100 ease-linear"
             :style="`width: ${progress}%`"></div>

        {{-- Product Image / Icon --}}
        <div class="shrink-0 relative">
            <template x-if="currentProduct && currentProduct.image">
                <img :src="getImageUrl(currentProduct.image)" :alt="currentProduct.name" class="w-13 h-13 rounded-xl object-cover border border-gray-100 shadow-sm" />
            </template>
            <template x-if="!currentProduct || !currentProduct.image">
                <div class="w-13 h-13 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </template>
            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">
                <i class="fa-solid fa-check"></i>
            </span>
        </div>

        {{-- Text Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1 mb-0.5">
                <p class="text-xs font-bold text-gray-900 truncate">
                    <span class="text-primary font-black" x-text="currentBuyer"></span>
                    <span class="text-gray-500 font-normal">telah checkout</span>
                </p>
                <span class="text-[10px] text-gray-400 shrink-0" x-text="timeAgo"></span>
            </div>

            <p class="text-xs font-bold text-gray-800 truncate leading-snug" x-text="currentProduct ? currentProduct.name : 'Sepatu Record'"></p>
            
            <p class="text-xs font-black text-accent mt-0.5" x-text="formattedPrice"></p>
        </div>

        {{-- Close Button --}}
        <button @click="hideToast()" class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1 text-xs">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
(function() {
    function initToastComponent() {
        if (typeof Alpine === 'undefined') return;

        Alpine.data('fakeOrderToast', (products) => ({
            show: false,
            progress: 100,
            currentBuyer: '',
            currentProduct: null,
            timeAgo: 'Baru saja',
            formattedPrice: '',
            timer: null,
            progressInterval: null,
            buyerNames: [
                'Mu****', 'Budi S****', 'Rizky A****', 'Dewi P****', 'Dimas K****',
                'Fikri H****', 'Anisa R****', 'Aditya N****', 'Bagus P****', 'Siti M****',
                'Hendra W****', 'Maya S****', 'Farhan M****', 'Nabila K****', 'Rian Z****',
                'Gilang P****', 'Putri A****', 'Ahmad T****', 'Lestari S****', 'Eko P****'
            ],
            timeAgos: ['Baru saja', '1 menit lalu', '2 menit lalu', '3 menit lalu', '5 menit lalu'],
            fallbackProducts: [
                { name: 'Sepatu Record Running Elite', price: 299000, image: null },
                { name: 'Sepatu Record Casual Classic', price: 349000, image: null },
                { name: 'Sepatu Record Sport Pro', price: 249000, image: null }
            ],

            init() {
                const productList = (products && products.length > 0) ? products : this.fallbackProducts;

                // Tampilkan notifikasi dalam 1.5 detik pertama
                setTimeout(() => {
                    this.triggerNextToast(productList);
                }, 1500);
            },

            triggerNextToast(productList) {
                const list = productList || ((products && products.length > 0) ? products : this.fallbackProducts);
                if (!list || list.length === 0) return;

                // Pick random buyer name & product
                this.currentBuyer = this.buyerNames[Math.floor(Math.random() * this.buyerNames.length)];
                this.currentProduct = list[Math.floor(Math.random() * list.length)];
                this.timeAgo = this.timeAgos[Math.floor(Math.random() * this.timeAgos.length)];
                this.formattedPrice = this.formatCurrency(this.currentProduct ? this.currentProduct.price : 299000);

                this.show = true;
                this.progress = 100;

                // Progress bar decay over 6 seconds
                const duration = 6000;
                const step = 50;
                let elapsed = 0;

                clearInterval(this.progressInterval);
                this.progressInterval = setInterval(() => {
                    elapsed += step;
                    this.progress = Math.max(0, 100 - (elapsed / duration) * 100);
                    if (elapsed >= duration) {
                        clearInterval(this.progressInterval);
                    }
                }, step);

                // Hide toast after 6 seconds
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.hideToast();
                    
                    // Schedule next random toast (setiap 30 - 60 detik)
                    const nextInterval = Math.floor(Math.random() * 30000) + 30000;
                    setTimeout(() => {
                        this.triggerNextToast(list);
                    }, nextInterval);

                }, duration);
            },

            hideToast() {
                this.show = false;
                clearInterval(this.progressInterval);
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            },

            getImageUrl(imagePath) {
                if (!imagePath) return '';
                if (imagePath.startsWith('http')) return imagePath;
                return '/storage/' + imagePath;
            }
        }));
    }

    if (window.Alpine) {
        initToastComponent();
    } else {
        document.addEventListener('alpine:init', initToastComponent);
    }
})();
</script>
