<?php
// Cek siapa yang sedang login dan apa perannya (role: admin atau pelanggan).
// Data login disimpan di $_SESSION oleh login-page/login.php.

function sudah_login()
{
    return !empty($_SESSION['id_pengguna']);
}

function adalah_admin()
{
    return sudah_login() && ($_SESSION['role'] ?? '') === 'admin';
}

// Halaman pembeli: kalau belum login, kirim ke halaman login dan ingat
// halaman yang tadi dituju supaya setelah login kembali ke sana.
function wajib_login()
{
    if (!sudah_login()) {
        set_pesan('gagal', 'Masuk dulu untuk melanjutkan.');
        arahkan(BASE_URL . 'login-page/login.php?lanjut=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

// Dipasang di baris pertama SETIAP halaman admin. Pengecekan ini yang melindungi
// folder admin, bukan pengalihan setelah login: tanpanya, pelanggan bisa membuka
// alamat /admin/... langsung dari browser.
function wajib_admin()
{
    wajib_login();
    if (!adalah_admin()) {
        set_pesan('gagal', 'Halaman itu khusus admin.');
        arahkan(BASE_URL . 'public/index.php');
    }
}

function id_pengguna()
{
    return (int) ($_SESSION['id_pengguna'] ?? 0);
}
