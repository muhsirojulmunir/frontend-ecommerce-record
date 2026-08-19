<x-app-layout>
    <x-slot name="title">About</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

        {{-- ===== VERSI BAHASA INGGRIS ===== --}}
        <section class="space-y-5 text-sm sm:text-base text-gray-700 leading-relaxed">
            <p>Record Shoes Official is a company engaged in the manufacture and distribution of quality
                footwear for men, women, and students. Founded with a passion for providing comfortable, durable,
                and stylish footwear, Record continues to innovate in design and technology to meet the needs of
                consumers throughout Indonesia.</p><br>

            <p>Since its inception, Record has been committed to being the Indonesian consumer's first choice for
                casual, school, and everyday footwear. Each of our products is designed with select materials,
                adheres to high-standard production processes, and pays attention to detail, ensuring comfort and
                durability, ensuring they accompany consumers in a variety of activities.</p><br>

            <p>Over its journey, Record has grown into a trusted local shoe brand, with a widespread distribution
                network spanning traditional markets, modern markets, and e-commerce platforms. We also continue
                to strengthen our presence through official Record stores in major cities, bringing our customers
                closer to us.</p><br>

            <p>More than just a shoe, Record is a symbol of surefootedness, authentic style, and enduring
                quality — because we believe every step tells a story, and Record is there to accompany every one.</p>

            <h2 class="font-bold text-primary uppercase tracking-wider mt-8 mb-2">Visi</h2>
            <p>To provide shoes with the highest quality and modern designs in line with current trends.</p>

            <h2 class="font-bold text-primary uppercase tracking-wider mt-8 mb-2">Misi</h2>
            <ul class="list-disc list-outside pl-5 space-y-1">
                <p>To provide shoes with the highest quality and modern designs in line with current trends.</p>
                <p>To expand distribution throughout Indonesia, both through offline and online sales.</p>
                <p>To continuously innovate in materials and technology to provide maximum comfort for every user.
                </p>
            </ul>
        </section>


        {{-- ===== VERSI BAHASA INDONESIA ===== --}}

        <section class="space-y-5 text-sm sm:text-base text-gray-700 leading-relaxed">
        <br><p>Record Shoes Official adalah perusahaan yang bergerak di bidang pembuatan dan distribusi
                alas kaki berkualitas untuk pria, wanita, dan pelajar. Berdiri dengan semangat menghadirkan
                sepatu yang nyaman, kuat, dan bergaya, Record terus berinovasi dalam desain dan teknologi
                untuk memenuhi kebutuhan konsumen di seluruh Indonesia.</p><br>

            <p>Sejak awal berdirinya, Record berkomitmen untuk menjadi pilihan utama masyarakat Indonesia dalam
                kategori sepatu kasual, sepatu sekolah, dan sepatu sehari-hari. Setiap produk kami dirancang
                dengan material pilihan, proses produksi yang terstandar tinggi, serta memperhatikan detail
                kenyamanan dan ketahanan agar dapat menemani langkah konsumen dalam berbagai aktivitas.</p><br>

            <p>Dalam perjalanannya, Record telah berkembang menjadi merek sepatu lokal yang dipercaya, dengan
                jaringan distribusi yang tersebar luas di pasar tradisional, modern, hingga platform e-commerce.
                Kami juga terus memperkuat kehadiran kami melalui toko-toko resmi Record di berbagai kota besar,
                agar lebih dekat dengan pelanggan kami.</p><br>

            <p>Lebih dari sekadar sepatu, Record adalah simbol dari langkah pasti, gaya autentik, dan kualitas
                yang tahan lama — karena kami percaya, setiap langkah memiliki cerita, dan Record hadir untuk
                menemani setiap langkah itu.</p>

            <h2 class="font-bold text-primary uppercase tracking-wider mt-8 mb-2">Visi</h2>
            <p>Menjadi merek sepatu lokal terdepan di Indonesia yang dikenal karena kualitas, kenyamanan, dan gaya
                yang sesuai kebutuhan semua kalangan.</p>

            <h2 class="font-bold text-primary uppercase tracking-wider mt-8 mb-2">Misi</h2>
            <ul class="list-disc list-outside pl-5 space-y-1">
                <p>Menghadirkan sepatu dengan kualitas terbaik dan desain modern sesuai tren masa kini.</p>
                <p>Memperluas jangkauan distribusi di seluruh Indonesia, baik melalui penjualan offline maupun
                    online.</p>
                <p>Terus berinovasi dalam material dan teknologi untuk memberikan kenyamanan maksimal bagi setiap
                pengguna.</p>
            </ul>
        </section>

        {{-- CTA ke katalog --}}
        <div class="mt-14 text-center">
            <a href="{{ route('products.index') }}"
                class="inline-block bg-primary hover:bg-primary-light text-white text-sm font-bold px-8 py-4 rounded-sm transition uppercase shadow-sm">
                Lihat Koleksi Kami
            </a>
        </div>
    </div>
</x-app-layout>