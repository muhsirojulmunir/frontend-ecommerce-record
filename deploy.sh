#!/bin/bash
# =====================================================================
#  Deploy Toko RECORD (frontend)
#  Jalankan di Terminal cPanel:  bash deploy.sh
#
#  BEDA PENTING DARI VERSI SEBELUMNYA:
#  Skrip ini TIDAK PERNAH menimpa berkas .env yang sudah ada.
#
#  Versi lama menulis ulang .env setiap kali dijalankan, lengkap dengan
#  DB_PASSWORD berisi teks "GANTI_DENGAN_PASSWORD_DATABASE_ANDA" dan APP_KEY
#  yang dibuat ulang terus-menerus. Akibatnya sambungan basis data putus —
#  halaman login tetap tampil karena tidak menyentuh basis data, tetapi begitu
#  tombol Masuk ditekan, pemeriksaan kata sandi menembak basis data dan
#  berujung layar 500. Semua kunci Midtrans dan Biteship pun ikut terhapus.
#
#  Sekarang: .env dibuat HANYA bila belum ada, dan sesudah itu tidak pernah
#  disentuh lagi. Rahasianya kamu isi sendiri sekali, lalu aman selamanya.
# =====================================================================

set -u

echo ""
echo "=================================================="
echo "  DEPLOY TOKO RECORD"
echo "=================================================="

# --- 1. Cari biner PHP yang benar --------------------------------------
# Versi PHP di terminal sering berbeda dari yang dipakai situs. Composer dan
# artisan harus memakai yang sama dengan situsnya, minimal 8.2.
PHP_BIN="php"
for kandidat in \
    /usr/local/bin/ea-php84 \
    /opt/cpanel/ea-php84/root/usr/bin/php \
    /opt/alt/php84/usr/bin/php \
    /usr/local/bin/ea-php83 \
    /opt/cpanel/ea-php83/root/usr/bin/php \
    /opt/alt/php83/usr/bin/php \
    /usr/local/bin/ea-php82 \
    /opt/cpanel/ea-php82/root/usr/bin/php
do
    if [ -x "$kandidat" ]; then PHP_BIN="$kandidat"; break; fi
done

echo ""
echo "PHP : $(${PHP_BIN} -v 2>/dev/null | head -n 1)"

VERSI=$(${PHP_BIN} -r 'echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;' 2>/dev/null || echo 0)
if [ "${VERSI}" -lt 82 ]; then
    echo "BERHENTI: butuh PHP 8.2 ke atas, yang terdeteksi terlalu lama."
    echo "Atur versinya di cPanel > MultiPHP Manager, lalu jalankan lagi."
    exit 1
fi

# --- 2. Siapkan .env, tanpa menimpa yang sudah ada ---------------------
if [ ! -f .env ]; then
    echo ""
    echo "Berkas .env belum ada — dibuatkan kerangkanya."

    if [ -f .env.contoh ]; then
        cp .env.contoh .env
    else
        echo "BERHENTI: .env.contoh tidak ditemukan. Unggah dulu berkas itu."
        exit 1
    fi

    echo ""
    echo "=================================================="
    echo "  ISI DULU BERKAS .env"
    echo "=================================================="
    echo "Buka .env lewat cPanel File Manager, lalu isi:"
    echo "  - DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo "  - MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY"
    echo "  - BITESHIP_API_KEY"
    echo ""
    echo "Sesudah terisi, jalankan lagi: bash deploy.sh"
    echo "=================================================="
    exit 0
fi

echo ".env sudah ada — TIDAK diubah."

# --- 3. Kunci aplikasi, dibuat hanya bila memang kosong ----------------
if grep -qE '^APP_KEY=base64:.+' .env; then
    echo "APP_KEY sudah terisi — dibiarkan."
else
    echo "APP_KEY kosong — dibuatkan."
    ${PHP_BIN} artisan key:generate --force
fi

# --- 4. Paket PHP ------------------------------------------------------
if [ ! -d vendor ]; then
    echo ""
    echo "Folder vendor belum ada — memasang paket..."

    if [ -f composer.phar ]; then
        ${PHP_BIN} -d memory_limit=-1 composer.phar install --no-dev --optimize-autoloader --no-interaction
    elif command -v composer >/dev/null 2>&1; then
        ${PHP_BIN} -d memory_limit=-1 "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction
    else
        echo "BERHENTI: composer tidak ditemukan. Unggah composer.phar ke folder ini."
        exit 1
    fi
else
    echo "Paket PHP sudah terpasang."
fi

# --- 5. Folder & izin --------------------------------------------------
echo ""
echo "Menyiapkan folder..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# --- 6. Tautan storage & basis data ------------------------------------
${PHP_BIN} artisan storage:link --force >/dev/null 2>&1 || true

echo "Menjalankan migrasi..."
${PHP_BIN} artisan migrate --force

# --- 7. Susun ulang cache ----------------------------------------------
echo ""
echo "Menyegarkan cache..."
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php \
      bootstrap/cache/services.php bootstrap/cache/packages.php

${PHP_BIN} artisan config:clear >/dev/null
${PHP_BIN} artisan route:clear  >/dev/null
${PHP_BIN} artisan view:clear   >/dev/null
${PHP_BIN} artisan cache:clear  >/dev/null 2>&1 || true

${PHP_BIN} artisan config:cache >/dev/null
${PHP_BIN} artisan route:cache  >/dev/null
${PHP_BIN} artisan view:cache   >/dev/null

# --- 8. Periksa hasilnya -----------------------------------------------
echo ""
${PHP_BIN} artisan record:periksa-hosting --perbaiki

echo ""
echo "=================================================="
echo "  Selesai. Buka: $(grep -E '^APP_URL=' .env | cut -d= -f2-)"
echo "=================================================="
echo ""
