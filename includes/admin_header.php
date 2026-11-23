<?php
// Kepala halaman admin: sidebar navy di kiri (desktop) atau menu geser (HP).
// Sebelum memanggil file ini, halaman admin mengisi:
//   $judul       judul tab dan judul halaman
//   $menu_aktif  nama menu yang sedang dibuka
// wajib_admin() dipanggil di sini, jadi setiap halaman admin otomatis terlindungi.
wajib_admin();

$judul_tab = isset($judul) ? $judul . ' | Admin ' . NAMA_TOKO : 'Admin ' . NAMA_TOKO;
$menu_aktif = $menu_aktif ?? '';

function menu_admin($nama, $teks, $berkas, $ikon, $aktif)
{
    $cur = $nama === $aktif ? ' aria-current="page"' : '';
    echo '<li><a class="' . ($nama === $aktif ? 'aktif' : '') . '"' . $cur . ' href="' . BASE_URL . 'admin/' . $berkas . '">'
        . ikon($ikon) . '<span>' . e($teks) . '</span></a></li>';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_tab) ?></title>
    <?php require __DIR__ . '/head_umum.php'; ?>
</head>
<body class="admin">
<a class="lewati" href="#isi">Lewati ke konten</a>

<div class="tata-admin">
    <?php /* Bilah atas khusus HP: tombol membuka sidebar. */ ?>
    <div class="atas-admin d-lg-none" style="grid-column:1/-1">
        <a class="merek" href="<?= BASE_URL ?>admin/index.php"><?= ikon_kristal() ?><span class="merek-teks">Admin</span></a>
        <button class="tombol-menu-admin" type="button" data-bs-toggle="offcanvas" data-bs-target="#sisiAdmin"
                aria-controls="sisiAdmin" aria-label="Buka menu admin"><?= ikon('list') ?> Menu</button>
    </div>

    <aside class="offcanvas-lg offcanvas-start sisi-admin" tabindex="-1" id="sisiAdmin" aria-label="Menu admin">
        <div class="offcanvas-header">
            <a class="merek" href="<?= BASE_URL ?>admin/index.php"><?= ikon_kristal() ?><span class="merek-teks">Admin Mlandang</span></a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sisiAdmin" aria-label="Tutup menu"></button>
        </div>
        <div class="offcanvas-body d-block">
            <nav aria-label="Navigasi admin">
                <ul class="menu-admin">
                    <?php
                    menu_admin('dashboard', 'Dashboard', 'index.php', 'speedometer2', $menu_aktif);
                    menu_admin('pesanan', 'Pesanan', 'pesanan.php', 'receipt', $menu_aktif);
                    menu_admin('produk', 'Produk', 'produk.php', 'box-seam', $menu_aktif);
                    menu_admin('kategori', 'Kategori', 'kategori.php', 'tags', $menu_aktif);
                    menu_admin('transaksi', 'Transaksi Stok', 'transaksi.php', 'arrow-left-right', $menu_aktif);
                    menu_admin('supplier', 'Supplier', 'supplier.php', 'truck', $menu_aktif);
                    menu_admin('pengguna', 'Pengguna', 'pengguna.php', 'people', $menu_aktif);
                    ?>
                    <li class="pemisah" role="separator"></li>
                    <li><a href="<?= BASE_URL ?>public/index.php"><?= ikon('shop') ?><span>Lihat Toko</span></a></li>
                    <li><a href="<?= BASE_URL ?>login-page/logout.php"><?= ikon('box-arrow-right') ?><span>Keluar</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <main id="isi" class="isi-admin">
        <?php tampil_pesan(); ?>
