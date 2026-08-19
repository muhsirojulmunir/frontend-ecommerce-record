/*
 * Pemampat berkas unggahan.
 *
 * Dipakai bersama oleh pengajuan pengembalian (foto resi, foto paket,
 * video unboxing) dan ulasan produk (foto ulasan).
 *
 * Ditaruh di public/js dan dimuat lewat <script src>, bukan lewat Vite.
 * CSS dan JS bundelan di proyek ini dibangun terpisah dan mudah tertinggal;
 * berkas ini harus langsung berlaku begitu disimpan.
 *
 * Dua hal yang dikerjakan:
 *   1. Foto  — digambar ulang ke canvas dengan sisi & mutu diturunkan
 *              bertahap sampai muat. Cepat, jalan di semua peramban.
 *   2. Video — disandikan ulang lewat MediaRecorder dengan bitrate yang
 *              dihitung dari durasi dan ukuran sasaran.
 *
 * Yang perlu diketahui tentang video: peramban tidak punya penyandi ulang
 * yang bekerja lebih cepat daripada waktu nyata. Videonya harus benar-benar
 * diputar dari awal sampai akhir untuk direkam ulang, jadi video 2 menit
 * memakan sekitar 2 menit. Karena itu prosesnya dilaporkan lewat bilah
 * kemajuan — tanpa itu pembeli akan mengira halamannya menggantung.
 */
(function () {
    'use strict';

    /** Menyalin Blob hasil pemampatan menjadi File agar bisa dikirim borang. */
    function jadikanBerkas(blob, nama, tipe) {
        return new File([blob], nama, { type: tipe, lastModified: Date.now() });
    }

    /** Mengganti nama berkas dengan akhiran baru, mempertahankan nama asalnya. */
    function gantiAkhiran(nama, akhiran) {
        return nama.replace(/\.[^.]+$/, '') + akhiran;
    }

    /* ══════════════════ Foto ══════════════════ */

    async function mampatkanFoto(berkas, opsi) {
        const sasaran = opsi.sasaranByte;
        const maksSisi = opsi.maksSisi || 2200;

        if (berkas.size <= sasaran) {
            return { berkas: berkas, dimampatkan: false };
        }

        const gambar = await createImageBitmap(berkas);

        let lebar = gambar.width;
        let tinggi = gambar.height;

        // Turunkan resolusi lebih dulu. Menurunkan mutu JPEG pada gambar
        // 4000 piksel menghasilkan berkas besar yang penuh artefak; mengecilkan
        // sisinya lebih dulu jauh lebih menguntungkan pada ukuran yang sama.
        const sisiTerpanjang = Math.max(lebar, tinggi);
        if (sisiTerpanjang > maksSisi) {
            const skala = maksSisi / sisiTerpanjang;
            lebar = Math.round(lebar * skala);
            tinggi = Math.round(tinggi * skala);
        }

        const kanvas = document.createElement('canvas');
        const konteks = kanvas.getContext('2d');

        let mutu = 0.86;
        let hasil = null;

        // Paling banyak delapan putaran: mutu diturunkan dulu, dan bila sudah
        // mentok di 0,4 barulah dimensinya ikut dipotong. Batas putaran ada
        // supaya gambar yang bandel tidak membuat peramban berputar tanpa henti.
        for (let putaran = 0; putaran < 8; putaran++) {
            kanvas.width = lebar;
            kanvas.height = tinggi;
            konteks.drawImage(gambar, 0, 0, lebar, tinggi);

            hasil = await new Promise(function (selesai) {
                kanvas.toBlob(selesai, 'image/jpeg', mutu);
            });

            if (!hasil) break;
            if (hasil.size <= sasaran) break;

            if (mutu > 0.42) {
                mutu -= 0.12;
            } else {
                lebar = Math.round(lebar * 0.8);
                tinggi = Math.round(tinggi * 0.8);
            }
        }

        gambar.close && gambar.close();

        if (!hasil || hasil.size > sasaran) {
            throw new Error('Fotonya tidak bisa dikecilkan sampai di bawah batas. '
                + 'Coba potret ulang dengan resolusi lebih rendah.');
        }

        return {
            berkas: jadikanBerkas(hasil, gantiAkhiran(berkas.nama || berkas.name, '.jpg'), 'image/jpeg'),
            dimampatkan: true,
            asal: berkas.size,
        };
    }

    /* ══════════════════ Video ══════════════════ */

    /**
     * Membaca durasi video, termasuk berkas yang menyembunyikannya.
     *
     * Sebagian berkas — terutama WebM hasil rekaman peramban dan video yang
     * dipotong aplikasi ponsel — melaporkan durasi Infinity karena panjangnya
     * tidak dicatat di kepala berkas. Cara membangunkannya adalah menyuruh
     * peramban mencari ke titik yang mustahil jauh: peramban terpaksa membaca
     * sampai ujung, lalu durasi yang sebenarnya muncul.
     *
     * Mengembalikan null bila tetap tidak terbaca.
     */
    function bacaDurasi(berkas) {
        return new Promise(function (selesai) {
            const video = document.createElement('video');
            const alamat = URL.createObjectURL(berkas);
            let sudah = false;

            function tuntas(nilai) {
                if (sudah) return;
                sudah = true;
                URL.revokeObjectURL(alamat);
                selesai(nilai);
            }

            video.preload = 'metadata';

            video.onloadedmetadata = function () {
                if (isFinite(video.duration) && video.duration > 0) {
                    tuntas(Math.round(video.duration));
                    return;
                }

                // Durasi belum diketahui: paksa peramban membaca sampai ujung.
                video.ontimeupdate = function () {
                    video.ontimeupdate = null;
                    video.currentTime = 0;
                    tuntas(isFinite(video.duration) && video.duration > 0
                        ? Math.round(video.duration)
                        : null);
                };
                video.currentTime = 1e101;
            };

            video.onerror = function () { tuntas(null); };

            // Jaring pengaman: berkas yang sangat besar bisa lama sekali dibaca,
            // dan menggantung tanpa batas jauh lebih buruk daripada melewatkan
            // pemeriksaan durasi.
            setTimeout(function () { tuntas(null); }, 15000);

            video.src = alamat;
        });
    }

    /*
     * Format rekaman yang didukung peramban ini.
     *
     * WebM didahulukan karena hasilnya paling kecil pada mutu yang sama.
     * MP4 disertakan sebagai cadangan untuk Safari, yang tidak bisa merekam
     * WebM sama sekali — tanpa cadangan ini pengguna iPhone tidak akan pernah
     * bisa mengecilkan videonya.
     */
    function tipeRekamanDidukung() {
        const kandidat = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4;codecs=avc1.42E01E,mp4a.40.2',
            'video/mp4',
        ];

        if (typeof MediaRecorder === 'undefined'
            || typeof MediaRecorder.isTypeSupported !== 'function') return null;

        for (let i = 0; i < kandidat.length; i++) {
            if (MediaRecorder.isTypeSupported(kandidat[i])) return kandidat[i];
        }
        return null;
    }

    /** Akhiran berkas yang sesuai dengan format rekamannya. */
    function akhiranUntuk(tipe) {
        return tipe.indexOf('mp4') !== -1 ? '.mp4' : '.webm';
    }

    /*
     * Syaratnya diperiksa terhadap apa yang BENAR-BENAR dipakai.
     *
     * Gambarnya disalin ke kanvas lalu kanvas itu yang direkam, jadi yang
     * dibutuhkan canvas.captureStream — bukan HTMLMediaElement.captureStream.
     * Keduanya sempat tertukar di sini, dan akibatnya Safari ditolak padahal
     * sebenarnya mampu.
     */
    function videoBisaDimampatkan() {
        return typeof MediaRecorder !== 'undefined'
            && typeof HTMLCanvasElement !== 'undefined'
            && typeof HTMLCanvasElement.prototype.captureStream === 'function'
            && tipeRekamanDidukung() !== null;
    }

    /**
     * Ukuran gambar yang pantas untuk jatah bitrate yang tersedia.
     *
     * Ini bagian yang paling menentukan hasilnya. Merekam ulang video 1080p
     * pada 600 kbps menghasilkan gambar yang hancur DAN kerap tetap melebihi
     * batas — penyandi dipaksa membuang detail di terlalu banyak piksel
     * sekaligus. Menurunkan resolusinya lebih dulu jauh lebih menguntungkan:
     * pada jumlah bita yang sama, 480p yang bersih jauh lebih terbaca
     * daripada 1080p yang penuh bercak.
     *
     * Patokannya 0,06 bit per piksel per bingkai pada 30 bingkai per detik.
     *
     * Angka ini sengaja tidak longgar. Dengan 0,1 — mutu "aman" untuk video
     * hiburan — video dua menit terpaksa turun sampai 240p, dan pada ukuran
     * itu jahitan atau cacat barang tidak lagi terlihat. Padahal justru itu
     * yang perlu dilihat admin. VP8/VP9 masih menghasilkan gambar yang layak
     * pada 0,06, jadi angka inilah yang dipakai: rekaman dua menit mendarat
     * di 360p, bukan 240p.
     */
    function tinggiYangPantas(bitVideo, tinggiAsli) {
        const pilihan = [1080, 720, 540, 480, 360, 240];
        const bingkaiPerDetik = 30;
        const bitPerPiksel = 0.06;

        for (let i = 0; i < pilihan.length; i++) {
            const t = pilihan[i];
            if (t > tinggiAsli) continue;           // jangan pernah memperbesar

            // Dianggap rasio 16:9 untuk perkiraan jumlah pikselnya
            const piksel = t * (t * 16 / 9);
            if (piksel * bingkaiPerDetik * bitPerPiksel <= bitVideo) return t;
        }

        return Math.min(tinggiAsli, 240);
    }

    /**
     * Menyandikan ulang video sampai muat ke ukuran sasaran.
     *
     * Peramban tidak punya penyandi yang bekerja lebih cepat daripada waktu
     * nyata: videonya harus benar-benar diputar dari awal sampai akhir untuk
     * direkam ulang. Video dua menit karena itu memakan sekitar dua menit.
     * Kemajuannya dilaporkan supaya penantian itu tidak terasa seperti
     * halaman yang menggantung.
     */
    async function sandikanUlang(berkas, opsi) {
        const tipe = tipeRekamanDidukung();
        const laporan = opsi.lapor || function () {};

        const video = document.createElement('video');
        const alamat = URL.createObjectURL(berkas);

        video.src = alamat;
        video.preload = 'auto';
        video.playsInline = true;

        const bersihkan = [];
        const tutup = function () {
            bersihkan.forEach(function (f) { try { f(); } catch (e) {} });
            URL.revokeObjectURL(alamat);
        };

        try {
            await new Promise(function (selesai, gagal) {
                video.onloadedmetadata = function () { selesai(); };
                video.onerror = function () { gagal(new Error('Videonya tidak bisa dibaca peramban.')); };
            });

            const tinggiAsli = video.videoHeight || 720;
            const lebarAsli  = video.videoWidth  || 1280;

            const tinggi = tinggiYangPantas(opsi.bitVideo, tinggiAsli);
            const skala  = tinggi / tinggiAsli;

            // Lebar dan tinggi dibulatkan ke bilangan genap. Penyandi video
            // bekerja dalam blok berukuran genap dan sebagian menolak ukuran
            // ganjil sama sekali.
            const lebar = Math.max(2, Math.round(lebarAsli * skala / 2) * 2);
            const tinggiGenap = Math.max(2, Math.round(tinggi / 2) * 2);

            const kanvas = document.createElement('canvas');
            kanvas.width = lebar;
            kanvas.height = tinggiGenap;
            const konteks = kanvas.getContext('2d');

            const arus = kanvas.captureStream(30);

            /*
             * Suara diambil lewat WebAudio, bukan dari elemen videonya.
             *
             * Dua hal sekaligus: suaranya tidak terdengar keras selama proses
             * berlangsung, dan yang terekam tetap suara asli pada volume
             * penuh. Seluruh bagian ini dibungkus penjagaan — video tanpa
             * suara, berkas yang formatnya aneh, atau peramban yang menolak
             * tidak boleh menggagalkan seluruh pemampatan. Gambar yang
             * terselamatkan tanpa suara jauh lebih berguna daripada
             * pengajuan yang tidak jadi terkirim.
             */
            let adaSuara = false;
            try {
                const KonteksAudio = window.AudioContext || window.webkitAudioContext;
                if (KonteksAudio) {
                    const konteksAudio = new KonteksAudio();
                    bersihkan.push(function () { konteksAudio.close(); });

                    // Tanpa resume(), konteksnya tetap tertidur dan yang
                    // terekam hanya kesenyapan.
                    if (konteksAudio.state === 'suspended') {
                        await konteksAudio.resume();
                    }

                    const sumber = konteksAudio.createMediaElementSource(video);
                    const tujuan = konteksAudio.createMediaStreamDestination();
                    sumber.connect(tujuan);

                    tujuan.stream.getAudioTracks().forEach(function (t) {
                        arus.addTrack(t);
                        adaSuara = true;
                    });
                }
            } catch (e) {
                adaSuara = false;
            }

            const pengaturan = { mimeType: tipe, videoBitsPerSecond: opsi.bitVideo };
            if (adaSuara) pengaturan.audioBitsPerSecond = opsi.bitAudio;

            const perekam = new MediaRecorder(arus, pengaturan);
            const potongan = [];

            perekam.ondataavailable = function (e) {
                if (e.data && e.data.size > 0) potongan.push(e.data);
            };

            const selesaiRekam = new Promise(function (selesai, gagal) {
                perekam.onstop = function () { selesai(new Blob(potongan, { type: tipe })); };
                perekam.onerror = function (e) { gagal(e.error || new Error('Perekaman gagal.')); };
            });

            // Gambar disalin ke kanvas bingkai demi bingkai. requestAnimationFrame
            // menyesuaikan diri dengan kemampuan perangkat; memaksa 30 bingkai
            // per detik di ponsel kelas bawah hanya membuat prosesnya tersendat.
            let jalan = true;
            const gambar = function () {
                if (! jalan) return;
                konteks.drawImage(video, 0, 0, lebar, tinggiGenap);
                requestAnimationFrame(gambar);
            };

            video.onended = function () {
                jalan = false;
                if (perekam.state !== 'inactive') perekam.stop();
            };

            video.ontimeupdate = function () {
                const panjang = isFinite(video.duration) && video.duration > 0
                    ? video.duration : opsi.durasi;
                laporan(Math.min(99, Math.round((video.currentTime / panjang) * 100)));
            };

            perekam.start(1000);
            laporan(0);

            try {
                await video.play();
            } catch (e) {
                /*
                 * Sebagian peramban menolak memutar video bersuara tanpa
                 * sentuhan pengguna. Dicoba ulang dalam keadaan bisu: suaranya
                 * hilang, tetapi videonya tetap bisa dikecilkan dan dikirim.
                 */
                video.muted = true;
                await video.play();
            }

            gambar();

            const hasil = await selesaiRekam;
            jalan = false;
            laporan(100);

            return hasil;
        } finally {
            tutup();
        }
    }

    async function mampatkanVideo(berkas, opsi) {
        const sasaran = opsi.sasaranByte;

        if (berkas.size <= sasaran) {
            return { berkas: berkas, dimampatkan: false };
        }

        if (!videoBisaDimampatkan()) {
            throw new Error('Peramban ini belum bisa mengecilkan video sendiri. '
                + 'Coba buka lewat Chrome, atau kecilkan videonya lebih dulu '
                + 'lalu unggah ulang.');
        }

        const durasiTerbaca = opsi.durasi != null ? opsi.durasi : await bacaDurasi(berkas);

        /*
         * Bitrate dihitung mundur dari ukuran sasaran:
         *
         *   bit tersedia = byte sasaran x 8
         *   bitrate      = bit tersedia / durasi
         *
         * Jatah suara dipatok kecil dan tetap — suara pada video unboxing
         * hanya perlu terdengar, bukan jernih — sisanya untuk gambar.
         *
         * Bila durasinya benar-benar tidak terbaca, dipakai batas durasi
         * terpanjang yang diizinkan sebagai anggapan. Itu menghasilkan bitrate
         * paling hemat: hasilnya lebih kecil daripada perlu, tetapi tidak
         * pernah melewati batas.
         */
        const durasi = (durasiTerbaca && durasiTerbaca > 0)
            ? durasiTerbaca
            : (opsi.durasiAnggapan || 120);

        const bitAudio = 64000;
        let bitVideo = Math.max(100000, Math.floor((sasaran * 8) / durasi) - bitAudio);

        let hasil = await sandikanUlang(berkas, {
            bitVideo: bitVideo, bitAudio: bitAudio,
            durasi: durasi, lapor: opsi.lapor,
        });

        /*
         * Bitrate yang diminta hanya sasaran, bukan janji: penyandi boleh
         * melesetinya, terutama pada gambar yang banyak bergerak. Kalau
         * hasilnya masih kebesaran, dicoba SEKALI lagi dengan jatah yang
         * dipotong sesuai kelebihannya.
         *
         * Sengaja hanya sekali. Tiap percobaan memakan waktu selama videonya,
         * dan menunggu tiga kali durasi video jauh lebih menyakitkan daripada
         * pesan yang jujur mengatakan videonya perlu dipendekkan.
         */
        if (hasil.size > opsi.batasByte) {
            const rasio = sasaran / hasil.size;
            bitVideo = Math.max(80000, Math.floor(bitVideo * rasio * 0.85));

            hasil = await sandikanUlang(berkas, {
                bitVideo: bitVideo, bitAudio: bitAudio,
                durasi: durasi, lapor: opsi.lapor,
            });
        }

        if (hasil.size > opsi.batasByte) {
            throw new Error('Videonya masih terlalu besar setelah dikecilkan dua kali. '
                + 'Coba rekam ulang lebih pendek, atau potong bagian setelah '
                + 'barangnya terlihat.');
        }

        const tipeHasil = tipeRekamanDidukung();

        return {
            berkas: jadikanBerkas(
                hasil,
                gantiAkhiran(berkas.name, akhiranUntuk(tipeHasil)),
                tipeHasil.split(';')[0]
            ),
            dimampatkan: true,
            asal: berkas.size,
        };
    }

    /*
     * Berkas hasil pemampatan dipasang kembali ke <input type="file"> lewat
     * DataTransfer. Tanpa ini borangnya tetap mengirim berkas asli yang besar,
     * dan seluruh pemampatan tadi jadi sia-sia.
     */
    function pasangKeMasukan(masukan, berkas) {
        const pemindah = new DataTransfer();
        pemindah.items.add(berkas);
        masukan.files = pemindah.files;
    }

    window.PemampatBerkas = {
        foto: mampatkanFoto,
        video: mampatkanVideo,
        durasi: bacaDurasi,
        videoDidukung: videoBisaDimampatkan,
        pasang: pasangKeMasukan,
    };
})();
