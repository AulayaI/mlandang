<?php
// Kerangka halaman login dan daftar: panel navy di kiri (hanya desktop) dan kartu form di kanan.
// Sebelum memanggil file ini, halaman mengisi $judul.
$judul_tab = $judul . ' | ' . NAMA_TOKO;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_tab) ?></title>
    <?php require __DIR__ . '/head_umum.php'; ?>
</head>
<body class="situs">
<a class="lewati" href="#isi">Lewati ke konten</a>
<div class="halaman-auth">
    <aside class="panel-auth" aria-hidden="false">
        <a class="merek" href="<?= BASE_URL ?>public/index.php"><?= ikon_kristal() ?><span class="merek-teks"><?= e(NAMA_TOKO) ?></span></a>
        <div>
            <h2>Stok beku praktis &amp; lezat untuk keluarga</h2>
            <p><?= e(TAGLINE) ?></p>
        </div>
        <p class="mb-0 small">Pesanan diambil sendiri di toko.</p>
    </aside>
    <main id="isi" class="latar-auth tekstur-es">
        <div class="kotak-auth">
            <a class="kembali-toko" href="<?= BASE_URL ?>public/index.php"><?= ikon('arrow-left') ?> Kembali ke toko</a>
