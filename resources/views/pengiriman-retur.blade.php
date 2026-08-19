<x-app-layout>
    <x-slot name="title">Pengiriman &amp; Pengembalian</x-slot>

    <x-halaman-teks judul="Pengiriman & Pengembalian"
        ringkas="Ketentuan pengiriman pesanan, serta cara menukar atau mengembalikan produk.">

        <h2>Pengiriman</h2>

        <h3>Waktu pemrosesan</h3>
        <p>
            Pesanan yang pembayarannya sudah kami terima akan diproses dalam
            <strong>1–2 hari kerja</strong>. Pesanan yang masuk di hari Minggu atau hari libur
            nasional akan diproses pada hari kerja berikutnya.
        </p>

        <h3>Estimasi waktu tiba</h3>
        <p>Setelah paket diserahkan ke kurir, perkiraan waktu tibanya:</p>
        <table>
            <thead>
                <tr>
                    <th>Wilayah</th>
                    <th>Estimasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Surabaya dan sekitarnya</td>
                    <td>1–2 hari kerja</td>
                </tr>
                <tr>
                    <td>Pulau Jawa</td>
                    <td>2–4 hari kerja</td>
                </tr>
                <tr>
                    <td>Luar Pulau Jawa</td>
                    <td>3–7 hari kerja</td>
                </tr>
            </tbody>
        </table>
        <p class="penting">
            Estimasi di atas dihitung dalam hari kerja dan di luar kendali kami sepenuhnya.
            Cuaca buruk, libur panjang, atau lonjakan pengiriman dapat memperlambat perjalanan paket.
        </p>

        <h3>Kurir dan ongkos kirim</h3>
        <p>
            Kami bekerja sama dengan <strong>JNE, J&amp;T Express, dan SiCepat</strong>.
            Ongkos kirim dihitung otomatis berdasarkan berat paket dan alamat tujuan,
            dan nominalnya tampil di halaman checkout sebelum kamu membayar.
        </p>

        <h3>Melacak pesanan</h3>
        <p>
            Nomor resi terbit setelah paket diserahkan ke kurir. Kamu bisa melihatnya di halaman
            <a href="{{ route('tracking') }}">Lacak Pesanan</a> setelah masuk ke akunmu.
        </p>

        <h2>Penukaran</h2>

        <h3>Syarat penukaran</h3>
        <p>Produk dapat ditukar ukuran atau warna dengan ketentuan berikut:</p>
        <ul>
            <li>Permintaan diajukan maksimal <strong>3 hari</strong> setelah paket diterima.</li>
            <li>Produk belum pernah dipakai di luar ruangan dan solnya masih bersih.</li>
            <li>Label, kartu, dan kotak asli masih lengkap serta tidak rusak.</li>
            <li>Produk yang dibeli dengan harga diskon khusus tidak dapat ditukar.</li>
        </ul>

        <h3>Cara mengajukan</h3>
        <ol>
            <li>Hubungi kami lewat WhatsApp dengan menyebutkan <strong>nomor pesanan</strong>.</li>
            <li>Sertakan foto produk dan alasan penukaran.</li>
            <li>Setelah kami setujui, kirim produk ke alamat yang kami berikan.</li>
            <li>Produk pengganti dikirim setelah barang lamamu kami terima dan diperiksa.</li>
        </ol>
        <p class="penting">
            Ongkos kirim penukaran ditanggung pembeli, kecuali penukaran terjadi karena
            kesalahan kami — misalnya salah kirim ukuran atau produk cacat.
        </p>

        <h2>Pengembalian &amp; Dana Kembali</h2>

        <h3>Produk cacat atau salah kirim</h3>
        <p>
            Kami mohon kamu <strong>merekam video saat membuka paket</strong>. Video ini menjadi
            bukti utama bila terjadi kekeliruan. Laporkan maksimal <strong>2 x 24 jam</strong>
            setelah paket diterima, dan kami akan menggantinya tanpa biaya tambahan.
        </p>

        <h3>Yang tidak dapat dikembalikan</h3>
        <ul>
            <li>Produk yang sudah dipakai, dicuci, atau diubah bentuknya.</li>
            <li>Produk rusak karena kesalahan pemakaian atau penyimpanan.</li>
            <li>Kaus kaki dan aksesori kaki lain, karena alasan kebersihan.</li>
            <li>Produk yang dilaporkan melewati batas waktu yang berlaku.</li>
        </ul>

        <h3>Proses pengembalian dana</h3>
        <p>
            Setelah barang retur kami terima dan lolos pemeriksaan, dana dikembalikan dalam
            <strong>3–7 hari kerja</strong> ke rekening bank yang kamu berikan. Ongkos kirim awal
            tidak termasuk dalam pengembalian, kecuali kesalahan berasal dari pihak kami.
        </p>

    </x-halaman-teks>
</x-app-layout>