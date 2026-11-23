<?php
// Baris pertama setiap halaman: pengaturan, session, database, fungsi bantu.
// Jalur memakai __DIR__ supaya benar dipanggil dari folder mana pun.

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/toko.php';
require_once __DIR__ . '/../config/gambar.php';

// Semua error yang tidak ditangkap berakhir di sini, supaya pengunjung
// melihat pesan sopan dan bukan potongan kode atau data koneksi.
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Terjadi kesalahan</title></head>'
        . '<body style="font-family:sans-serif;max-width:36rem;margin:3rem auto;padding:0 1rem">'
        . '<h1>Terjadi kesalahan</h1>'
        . '<p>Halaman belum bisa dimuat. Coba muat ulang beberapa saat lagi.</p>';
    if (MODE_DEBUG) {
        echo '<pre style="white-space:pre-wrap">' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
    echo '</body></html>';
});

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/fungsi.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/komponen.php';
require_once __DIR__ . '/keranjang.php';
require_once __DIR__ . '/pesanan.php';

// Batalkan otomatis pesanan yang lewat batas bayar (lihat includes/pesanan.php).
kedaluwarsakan_pesanan_lama();
