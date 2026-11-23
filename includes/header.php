<?php
// Kepala halaman untuk sisi pembeli: logo, ikon akun, ikon keranjang, dan tab menu.
// Sebelum memanggil file ini, halaman mengisi:
//   $judul       judul tab browser (tanpa nama toko)
//   $menu_aktif  'beranda', 'produk', 'tentang', atau 'lokasi' (menandai tab)
$judul_tab = isset($judul) ? $judul . ' | ' . NAMA_TOKO : NAMA_TOKO;
$menu_aktif = $menu_aktif ?? '';
$jml_keranjang = jumlah_keranjang();

// Satu tab menu. Tab yang sedang dibuka diberi class "aktif".
function tab_menu($nama, $teks, $url, $aktif)
{
    $cur = $nama === $aktif ? ' aria-current="page"' : '';
    echo '<li><a class="tab' . ($nama === $aktif ? ' aktif' : '') . '" data-tab="' . $nama . '"' . $cur . ' href="' . $url . '">' . e($teks) . '</a></li>';
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
<body class="situs">
<a class="lewati" href="#isi">Lewati ke konten</a>

<header class="bar-atas">
    <div class="container">
        <div class="baris-merek">
            <a class="merek" href="<?= BASE_URL ?>public/index.php">
                <?= ikon_kristal() ?>
                <span class="merek-teks"><?= e(NAMA_TOKO) ?></span>
            </a>
            <div class="ikon-bar">
                <?php if (sudah_login()): ?>
                    <div class="dropdown">
                        <button class="tombol-ikon" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="Menu akun <?= e($_SESSION['nama']) ?>"><?= ikon('person') ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">Masuk sebagai <strong><?= e($_SESSION['nama']) ?></strong></span></li>
                            <?php if (adalah_admin()): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/index.php">Dashboard Admin</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>public/pesanan_saya.php">Pesanan Saya</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>login-page/logout.php">Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="tombol-ikon" href="<?= BASE_URL ?>login-page/login.php" aria-label="Masuk atau daftar"><?= ikon('person') ?></a>
                <?php endif; ?>
                <a class="tombol-ikon" href="<?= BASE_URL ?>public/keranjang.php"
                   aria-label="Keranjang belanja, <?= $jml_keranjang ?> produk"><?= ikon('cart3') ?>
                    <span class="jumlah-keranjang" id="lencana-keranjang"<?= $jml_keranjang > 0 ? '' : ' hidden' ?>><?= $jml_keranjang ?></span>
                </a>
            </div>
        </div>
        <nav aria-label="Menu utama">
            <ul class="tab-menu">
                <?php
                tab_menu('beranda', 'Beranda', BASE_URL . 'public/index.php', $menu_aktif);
                tab_menu('produk', 'Produk', BASE_URL . 'public/produk.php', $menu_aktif);
                tab_menu('tentang', 'Tentang Kami', BASE_URL . 'public/index.php#tentang', $menu_aktif);
                tab_menu('lokasi', 'Lokasi', BASE_URL . 'public/index.php#lokasi', $menu_aktif);
                ?>
            </ul>
        </nav>
    </div>
</header>

<main id="isi">
