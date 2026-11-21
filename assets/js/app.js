/* BUDI FROZEN - interaksi ringan tanpa framework */
(function () {
    'use strict';

    // Sidebar mobile
    var body = document.body;
    var toggle = document.getElementById('menuToggle');
    var backdrop = document.getElementById('sidebarBackdrop');

    if (toggle) {
        toggle.addEventListener('click', function () {
            body.classList.toggle('sidebar-open');
        });
    }
    if (backdrop) {
        backdrop.addEventListener('click', function () {
            body.classList.remove('sidebar-open');
        });
    }

    // Tutup alert otomatis
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        var close = el.querySelector('.alert-close');
        var hide = function () {
            el.style.transition = 'opacity .25s ease, transform .25s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';
            setTimeout(function () { el.remove(); }, 260);
        };
        if (close) close.addEventListener('click', hide);
        setTimeout(hide, 6000);
    });

    // Konfirmasi hapus
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                ev.preventDefault();
            }
        });
    });

    // Preview nilai stok di form produk
    var harga = document.getElementById('harga');
    var stok = document.getElementById('stok');
    var preview = document.getElementById('nilaiStokPreview');

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungNilaiStok() {
        if (!harga || !stok || !preview) return;
        var h = parseFloat(harga.value) || 0;
        var s = parseInt(stok.value, 10) || 0;
        preview.textContent = formatRupiah(h * s);
    }

    [harga, stok].forEach(function (input) {
        if (input) input.addEventListener('input', hitungNilaiStok);
    });
    hitungNilaiStok();
})();
