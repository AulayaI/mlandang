<?php
// Pengaturan dasar yang dibaca semua halaman.

date_default_timezone_set('Asia/Jakarta');

// true: pesan error lengkap ditampilkan (enak untuk belajar di Laragon).
// Ubah ke false setelah project diunggah ke hosting, karena pesan error
// bisa memuat nama user dan host database.
define('MODE_DEBUG', true);

// BASE_URL adalah alamat folder project di mata browser, misalnya '/' atau
// '/X1-FF-PA_Ly/'. Dihitung otomatis supaya kode yang sama jalan di localhost,
// di domain utama, dan di subfolder hosting. Kalau di hosting hasilnya salah,
// isi manual di sini, contoh: $base_manual = '/X1-FF-PA_Ly/';
$base_manual = '';

if ($base_manual !== '') {
    $base = $base_manual;
} else {
    $akar_project = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
    $akar_web = '';
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $akar_web = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']));
    }
    $base = '/';
    if ($akar_web !== '' && stripos($akar_project, $akar_web) === 0) {
        $base = substr($akar_project, strlen($akar_web)) . '/';
    }
}
define('BASE_URL', $base);

// Foto produk: jalur di disk (untuk menyimpan) dan jalur URL (untuk ditampilkan).
define('DIR_FOTO_PRODUK', str_replace('\\', '/', (string) realpath(__DIR__ . '/..')) . '/assets/img/produk/');
define('URL_FOTO_PRODUK', BASE_URL . 'assets/img/produk/');
