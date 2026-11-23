<?php
// Landing page toko: hero dengan slider gambar, kategori, produk terbaru, tentang, keunggulan, cara pesan, lokasi.
require_once __DIR__ . '/../includes/awal.php';

// Kategori diambil dari produk yang aktif, jadi kategori tanpa produk tidak tampil.
// Gambar tiap kategori diunggah admin lewat Admin > Kategori.
$kategori = db_semua(
    "SELECT kategori AS nama_kategori, COUNT(*) AS jumlah
     FROM produk
     WHERE status = 'aktif'
     GROUP BY kategori
     ORDER BY kategori"
);

// Slide gambar buatan sendiri (hero-1 sampai hero-3). Yang file-nya belum ada dilewati,
// kecuali hero-1 yang selalu tampil (sebagai placeholder) supaya penempatannya terlihat.
$slide_gambar = [];
foreach (['hero-1', 'hero-2', 'hero-3'] as $n) {
    if ($n === 'hero-1' || url_gambar_tampilan($n) !== '') {
        $slide_gambar[] = $n;
    }
}
$jumlah_slide = count($slide_gambar);

$terbaru = db_semua(
    "SELECT p.*, p.kategori AS nama_kategori
     FROM produk p
     WHERE p.status = 'aktif'
     ORDER BY p.id_produk DESC LIMIT 6"
);

$judul = 'Beranda';
$menu_aktif = 'beranda';
$footer_penuh = true;
require __DIR__ . '/../includes/header.php';
?>

<section class="hero" aria-label="Pengantar toko">
    <div class="container">
        <?php tampil_pesan(); ?>
        <div class="teks-hero">
            <h1>Stok Beku <span class="biru">Praktis &amp; Lezat</span> untuk Keluarga</h1>
            <p class="tagline"><?= e(TAGLINE) ?></p>
        </div>

        <?php /* Slider jQuery (lihat bagian 1 di assets/js/main.js). Isinya gambar hero-1 sampai hero-3 buatanmu. */ ?>
        <div class="slider" id="slider" role="region" aria-roledescription="carousel" aria-label="Gambar toko">
            <div class="slide-daftar">
                <?php $no = 0; foreach ($slide_gambar as $n): ?>
                    <figure class="slide slide-hero<?= $no === 0 ? ' aktif' : '' ?>" role="group" aria-roledescription="slide"
                            aria-label="<?= $no + 1 ?> dari <?= $jumlah_slide ?>" aria-hidden="<?= $no === 0 ? 'false' : 'true' ?>">
                        <?= gambar_slot($n, 'Foto beranda ' . ($no + 1), 'gambar-hero') ?>
                    </figure>
                <?php $no++; endforeach; ?>
            </div>
            <?php if ($jumlah_slide > 1): ?>
                <div class="titik-daftar">
                    <?php for ($i = 0; $i < $jumlah_slide; $i++): ?>
                        <button type="button" class="titik<?= $i === 0 ? ' aktif' : '' ?>" data-ke="<?= $i ?>" aria-label="Tampilkan gambar ke-<?= $i + 1 ?>"></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tombol-hero">
            <a class="btn btn-aksen" href="<?= BASE_URL ?>public/produk.php">Belanja Sekarang</a>
            <a class="btn btn-garis" href="#cara">Cara Memesan</a>
        </div>
    </div>
</section>

<section class="seksi muncul" aria-labelledby="judul-kategori">
    <div class="container">
        <h2 class="judul-seksi" id="judul-kategori"><?= ikon_kristal() ?> Kategori Pilihan</h2>
        <?php if ($kategori): ?>
            <ul class="daftar-kategori">
                <?php foreach ($kategori as $k): ?>
                    <li>
                        <a href="<?= BASE_URL ?>public/produk.php?kategori=<?= urlencode($k['nama_kategori']) ?>">
                            <?= gambar_slot('kategori-' . slug($k['nama_kategori']), 'Kategori ' . $k['nama_kategori'], 'gambar-kategori') ?>
                            <span class="teks-kategori">
                                <strong><?= e($k['nama_kategori']) ?></strong>
                                <small><?= (int) $k['jumlah'] ?> produk</small>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="kosong">Kategori produk belum ditambahkan.</p>
        <?php endif; ?>
    </div>
</section>

<section class="seksi muncul" aria-labelledby="judul-terbaru">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2">
            <h2 class="judul-seksi" id="judul-terbaru"><?= ikon_kristal() ?> Produk Terbaru &amp; Unggulan</h2>
            <a class="lihat-semua" href="<?= BASE_URL ?>public/produk.php">Lihat semua produk</a>
        </div>
        <?php if ($terbaru): ?>
            <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-3 baris-terbaru">
                <?php foreach ($terbaru as $p): ?>
                    <div class="col"><?php kartu_produk($p); ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="kosong">Belum ada produk yang dijual. Silakan kembali lagi nanti.</p>
        <?php endif; ?>
    </div>
</section>

<?php /* Blok navy: Tentang Kami dan Keunggulan. Tepi atas bergerigi diatur CSS (.blok-navy). */ ?>
<div class="blok-navy">
    <section class="tentang muncul" id="tentang" aria-labelledby="judul-tentang">
        <div class="container">
            <h2 class="judul-seksi" id="judul-tentang"><?= ikon_kristal() ?> Tentang Kami</h2>
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="kolase">
                        <?= gambar_slot('tentang-1', 'Suasana belanja frozen food', 'k1') ?>
                        <?= gambar_slot('tentang-2', 'Stok frozen food di freezer', 'k2') ?>
                        <?= gambar_slot('tentang-3', 'Aneka produk frozen food', 'k3') ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="blok-teks-tentang">
                        <h3 class="judul-tentang">Tentang <span class="biru-muda"><?= e(NAMA_TOKO) ?></span></h3>
                        <p class="teks-tentang"><?= e(TENTANG_TOKO) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="unggul muncul" aria-labelledby="judul-unggul">
        <div class="container">
            <h2 class="judul-seksi" id="judul-unggul"><?= ikon_kristal() ?> Keunggulan Toko</h2>
            <ul class="daftar-unggul">
                <li><?= ikon('snow2') ?><div><strong>Jam Buka Toko</strong><span>Buka Pukul 08.00 - 21.00.</span></div></li>
                <li class="oranye"><?= ikon('calendar-check') ?><div><strong>Kedaluwarsa Tercantum</strong><span>Tertera di halaman produk.</span></div></li>
                <li><?= ikon('calculator') ?><div><strong>Total Otomatis</strong><span>Dihitung langsung di keranjang.</span></div></li>
                <li class="oranye"><?= ikon('qr-code') ?><div><strong>Bayar QRIS atau Transfer</strong><span>Konfirmasi lewat WhatsApp.</span></div></li>
            </ul>
        </div>
    </section>
</div>

<section class="seksi muncul" id="cara" aria-labelledby="judul-cara">
    <div class="container">
        <h2 class="judul-seksi" id="judul-cara"><?= ikon_kristal() ?> Cara Pemesanan</h2>
        <ol class="langkah">
            <li><span class="nomor">1</span><strong>Pilih Produk</strong></li>
            <li><span class="nomor">2</span><strong>Bayar</strong></li>
            <li><span class="nomor">3</span><strong>Konfirmasi WA</strong></li>
            <li><span class="nomor">4</span><strong>Ambil di Toko</strong></li>
        </ol>
        <p class="catatan-langkah mb-0">Masuk atau daftar akun dulu untuk memesan. Melihat produk tidak perlu akun.</p>
    </div>
</section>

<section class="seksi muncul" id="lokasi" aria-labelledby="judul-lokasi">
    <div class="container">
        <h2 class="judul-seksi" id="judul-lokasi"><?= ikon_kristal() ?> Lokasi Toko</h2>
        <div class="kartu-lokasi"><div class="row g-3 align-items-stretch">
            <div class="col-md-6">
                <div class="peta">
                    <iframe title="Peta lokasi toko di Google Maps" src="<?= e(EMBED_PETA) ?>" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-lokasi">
                    <ul class="baris-lokasi">
                        <li><span class="label-kecil"><?= ikon('geo-alt-fill') ?> Alamat</span><strong><?= e(ALAMAT_TOKO) ?></strong></li>
                        <li><span class="label-kecil"><?= ikon('bag-check') ?> Pengambilan pesanan</span><strong>Diambil sendiri di toko</strong></li>
                        <li><span class="label-kecil"><?= ikon('whatsapp') ?> WhatsApp</span>
                            <a href="<?= e(link_wa(NOMOR_WA_TOKO, 'Halo ' . NAMA_TOKO . ', saya mau tanya tentang produk.')) ?>" target="_blank" rel="noopener noreferrer"><strong>Chat penjual</strong></a></li>
                    </ul>
                    <a class="btn btn-garis" href="<?= e(LINK_GOOGLE_MAPS) ?>" target="_blank" rel="noopener noreferrer"><?= ikon('box-arrow-up-right') ?> Lihat di Google Maps</a>
                </div>
            </div>
        </div></div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
