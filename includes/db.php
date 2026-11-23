<?php
// Satu-satunya file yang bicara langsung dengan MySQL.
// Halaman lain memanggil db_semua(), db_satu(), db_jalankan().
//
// Semua query memakai prepared statement: nilai dari pengguna tidak pernah
// ditempel ke teks SQL, jadi tidak bisa menyisipkan perintah (SQL Injection).
// Tanda ? adalah tempat nilai. Huruf pada $tipe menyebut jenis tiap nilai:
// i = angka bulat, d = desimal, s = teks.

require_once __DIR__ . '/../config/koneksi.php';

// Dengan mode ini, error SQL menjadi exception, bukan diam-diam gagal.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAMA);
mysqli_set_charset($koneksi, 'utf8mb4');

function db_eksekusi($sql, $tipe = '', $param = [])
{
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, $sql);
    if ($tipe !== '') {
        mysqli_stmt_bind_param($stmt, $tipe, ...$param);
    }
    mysqli_stmt_execute($stmt);
    return $stmt;
}

// Semua baris hasil SELECT sebagai array.
function db_semua($sql, $tipe = '', $param = [])
{
    $stmt = db_eksekusi($sql, $tipe, $param);
    $hasil = mysqli_stmt_get_result($stmt);
    $baris = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $baris;
}

// Satu baris pertama, atau null kalau tidak ada.
function db_satu($sql, $tipe = '', $param = [])
{
    $baris = db_semua($sql, $tipe, $param);
    return $baris[0] ?? null;
}

// INSERT, UPDATE, DELETE. Mengembalikan jumlah baris yang terpengaruh.
function db_jalankan($sql, $tipe = '', $param = [])
{
    $stmt = db_eksekusi($sql, $tipe, $param);
    $jumlah = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $jumlah;
}

function db_id_terakhir()
{
    global $koneksi;
    return mysqli_insert_id($koneksi);
}

// Transaksi: beberapa perubahan dianggap satu kesatuan. Kalau salah satu gagal,
// semuanya dibatalkan, jadi stok dan pesanan tidak pernah setengah tersimpan.
function db_mulai()
{
    global $koneksi;
    mysqli_begin_transaction($koneksi);
}

function db_simpan()
{
    global $koneksi;
    mysqli_commit($koneksi);
}

function db_batal()
{
    global $koneksi;
    mysqli_rollback($koneksi);
}
