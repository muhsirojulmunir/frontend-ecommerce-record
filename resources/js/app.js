

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Animasi "terbang ke keranjang": mengkloning elemen gambar produk lalu
 * menerbangkannya secara visual menuju ikon keranjang di navbar, mengecil
 * dan memudar di sepanjang jalan. Dipanggil setiap kali produk berhasil
 * ditambahkan ke keranjang lewat AJAX.
 */
window.flyToCart = function (imgEl) {
    if (!imgEl) return;

    // Ambil ikon keranjang yang sedang terlihat (desktop atau mobile, tergantung ukuran layar)
    const cartIcon = Array.from(document.querySelectorAll('[data-cart-icon]'))
        .find((el) => el.getBoundingClientRect().width > 0);
    if (!cartIcon) return;

    const startRect = imgEl.getBoundingClientRect();
    const endRect = cartIcon.getBoundingClientRect();

    const flyer = imgEl.cloneNode(true);
    flyer.style.position = 'fixed';
    flyer.style.top = startRect.top + 'px';
    flyer.style.left = startRect.left + 'px';
    flyer.style.width = startRect.width + 'px';
    flyer.style.height = startRect.height + 'px';
    flyer.style.objectFit = 'cover';
    flyer.style.borderRadius = '8px';
    flyer.style.zIndex = '9999';
    flyer.style.pointerEvents = 'none';
    flyer.style.margin = '0';
    flyer.style.boxShadow = '0 12px 30px rgba(0,0,0,0.3)';
    flyer.style.transition = 'top 700ms cubic-bezier(0.55,0,0.1,1), left 700ms cubic-bezier(0.55,0,0.1,1), width 700ms cubic-bezier(0.55,0,0.1,1), height 700ms cubic-bezier(0.55,0,0.1,1), opacity 700ms ease-in, transform 700ms cubic-bezier(0.55,0,0.1,1)';
    document.body.appendChild(flyer);

    const endTop = endRect.top + endRect.height / 2 - 10;
    const endLeft = endRect.left + endRect.width / 2 - 10;

    // Dua requestAnimationFrame supaya browser sempat "commit" posisi awal
    // sebelum posisi akhir diterapkan, agar transisi CSS benar-benar berjalan.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            flyer.style.top = endTop + 'px';
            flyer.style.left = endLeft + 'px';
            flyer.style.width = '20px';
            flyer.style.height = '20px';
            flyer.style.opacity = '0.4';
            flyer.style.transform = 'rotate(15deg)';
        });
    });

    setTimeout(() => {
        flyer.remove();
        window.dispatchEvent(new CustomEvent('cart-bump'));
    }, 700);
};
