<?php
// Data toko. Nilai bertanda CONTOH belum asli. Saat data asli sudah ada,
// ganti di file ini saja, halaman lain ikut berubah.

define('NAMA_TOKO', 'Mlandang Frozen Food');
define('TAGLINE', 'Nugget, sosis, dan bakso beku untuk stok rumah dan reseller.');

// Teks bagian "Tentang toko". Isinya bersumber dari wawancara dengan pemilik usaha.
define('TENTANG_TOKO', 'Mlandang Frozen Food menjual nugget, sosis, bakso beku, kentang beku, dan aneka frozen food lain. Pembelinya warga sekitar dan reseller. Usaha ini berjalan sejak Juni 2026. Lewat website ini, pembeli bisa melihat daftar produk, memesan, dan membayar tanpa harus menghubungi toko satu per satu.');

// CONTOH. Format internasional tanpa tanda + dan tanpa 0 di depan,
// misalnya 6281234567890. Nomor ini dipakai tombol WhatsApp dan konfirmasi bayar.
define('NOMOR_WA_TOKO', '6281259204220');

// Batas waktu pembayaran (menit). Pesanan yang belum dikonfirmasi lewat tombol
// "Sudah Bayar" sampai batas ini otomatis dibatalkan dan stoknya kembali.
define('BATAS_BAYAR_MENIT', 10);

// Akun media sosial untuk footer beranda. Ganti dengan link akun toko, contoh
// 'https://www.instagram.com/namaakun'. Kalau dikosongkan, ikonnya tidak tampil.
define('LINK_INSTAGRAM', 'https://www.instagram.com/');
define('LINK_TIKTOK', 'https://www.tiktok.com/@mlandangff');

// CONTOH. Alamat yang tampil di bagian Lokasi dan footer.
define('ALAMAT_TOKO', '8WG6+8HV, Babadan, Kec. Pace, Kabupaten Nganjuk, Jawa Timur 64472');

// Kata kunci untuk peta. Isi dengan alamat atau nama tempat di Google Maps.
define('KATA_KUNCI_PETA', '-7.674127629053657, 111.9114002907133');
define('LINK_GOOGLE_MAPS', 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(KATA_KUNCI_PETA));
define('EMBED_PETA', 'https://www.google.com/maps?q=' . rawurlencode(KATA_KUNCI_PETA) . '&output=embed');

// CONTOH. Gambar QRIS toko. Ganti file ini dengan gambar QRIS asli
// (boleh png atau jpg, lalu sesuaikan namanya di sini).
define('GAMBAR_QRIS', 'assets/img/qris.jpg');

// CONTOH. Tujuan transfer e-wallet.
define('EWALLET_NAMA', 'DANA');
define('EWALLET_NOMOR', '081259204220');
define('EWALLET_ATAS_NAMA', 'Mlandang Frozen Food');

// Produk dengan stok sama dengan atau di bawah angka ini muncul di daftar "stok menipis".
define('BATAS_STOK_MENIPIS', 10);

// true: halaman pembayaran menampilkan peringatan bahwa ini demo.
// Ubah ke false hanya kalau QRIS dan nomor e-wallet di atas sudah asli
// dan toko siap menerima pesanan sungguhan.
define('MODE_DEMO', false);
