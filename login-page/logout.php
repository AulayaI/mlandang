<?php
require_once __DIR__ . '/../includes/awal.php';

// Isi session dikosongkan dan ID-nya diganti. Session tidak dihancurkan total
// supaya pesan "sudah keluar" di bawah masih bisa tampil di halaman berikutnya.
$_SESSION = [];
session_regenerate_id(true);
set_pesan('sukses', 'Kamu sudah keluar dari akun.');
arahkan(BASE_URL . 'public/index.php');
