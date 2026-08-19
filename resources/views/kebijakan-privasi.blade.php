<x-app-layout>
    <x-slot name="title">Kebijakan Privasi</x-slot>

    <x-halaman-teks judul="Kebijakan Privasi"
        ringkas="Bagaimana kami mengumpulkan, memakai, dan menjaga data pribadimu.">

        <p>
            Kebijakan ini menjelaskan cara <strong>RECORD</strong> menangani data pribadi
            pengunjung dan pembeli di website ini. Dengan menggunakan layanan kami, kamu
            dianggap memahami dan menyetujui ketentuan di bawah ini.
        </p>

        <h2>Data yang Kami Kumpulkan</h2>
        <ul>
            <li><strong>Data akun</strong> — nama, alamat email, nomor telepon, dan kata sandi.</li>
            <li><strong>Data pengiriman</strong> — nama penerima, alamat lengkap, dan nomor telepon.</li>
            <li><strong>Data pesanan</strong> — produk yang dibeli, nominal, dan metode pembayaran.</li>
            <li><strong>Data teknis</strong> — alamat IP, jenis perangkat, dan halaman yang dikunjungi.</li>
        </ul>
        <p class="penting">
            <strong>Kami tidak pernah menyimpan nomor kartu, PIN, maupun kode OTP.</strong>
            Seluruh proses pembayaran ditangani langsung oleh penyedia pembayaran resmi.
        </p>

        <h2>Cara Kami Menggunakannya</h2>
        <ul>
            <li>Memproses pesanan dan mengirimkan paket ke alamatmu.</li>
            <li>Memberi kabar mengenai status pesanan dan pengiriman.</li>
            <li>Menjawab pertanyaan dan keluhan yang kamu sampaikan.</li>
            <li>Memperbaiki tampilan serta kenyamanan penggunaan website.</li>
            <li>Mengirim penawaran, hanya bila kamu menyetujuinya.</li>
        </ul>

        <h2>Berbagi Data dengan Pihak Lain</h2>
        <p>Kami hanya membagikan data seperlunya kepada:</p>
        <ul>
            <li><strong>Jasa kurir</strong> — nama, alamat, dan nomor telepon penerima, agar paket sampai.</li>
            <li><strong>Penyedia pembayaran</strong> — untuk memproses transaksi dengan aman.</li>
            <li><strong>Aparat berwenang</strong> — bila diwajibkan oleh peraturan yang berlaku.</li>
        </ul>
        <p>
            Kami <strong>tidak menjual maupun menyewakan</strong> data pribadimu kepada siapa pun
            untuk keperluan iklan pihak ketiga.
        </p>

        <h2>Keamanan Data</h2>
        <p>
            Kata sandi disimpan dalam bentuk terenkripsi dan tidak dapat dibaca oleh siapa pun,
            termasuk oleh kami. Akses ke data pesanan dibatasi hanya untuk staf yang
            membutuhkannya. Meski begitu, tidak ada sistem yang sepenuhnya kebal, sehingga
            kami mengimbau kamu memakai kata sandi yang kuat dan tidak membagikannya.
        </p>

        <h2>Cookie</h2>
        <p>
            Website ini memakai cookie untuk menjaga sesi login dan mengingat isi keranjang
            belanjamu. Cookie dapat kamu hapus lewat pengaturan browser, namun beberapa fitur
            mungkin tidak berjalan semestinya setelahnya.
        </p>

        <h2>Hak Kamu atas Data</h2>
        <ul>
            <li>Melihat dan memperbarui data akun lewat halaman <strong>Profil</strong>.</li>
            <li>Meminta penghapusan akun beserta datanya.</li>
            <li>Berhenti menerima penawaran kapan saja.</li>
        </ul>
        <p>
            Penghapusan akun bersifat permanen dan riwayat pesanan akan ikut terhapus.
            Sebagian data transaksi mungkin tetap kami simpan bila diwajibkan oleh
            peraturan perpajakan atau pembukuan.
        </p>

        <h2>Perubahan Kebijakan</h2>
        <p>
            Kebijakan ini dapat kami perbarui sewaktu-waktu. Tanggal pembaruan terakhir
            tercantum di bagian atas halaman. Perubahan berlaku sejak dipublikasikan.
        </p>

        <h2>Menghubungi Kami</h2>
        <p>
            Bila ada pertanyaan mengenai kebijakan ini, hubungi kami lewat
            <a href="{{ route('kontak') }}">halaman kontak</a> atau WhatsApp di
            +62 813-2306-5554.
        </p>

    </x-halaman-teks>
</x-app-layout>
