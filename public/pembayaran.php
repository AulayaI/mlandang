<?php
// Halaman pembayaran sekaligus detail pesanan. Hanya pemilik pesanan dan admin yang boleh melihat.
require_once __DIR__ . '/../includes/awal.php';
wajib_login();

$kode = trim($_GET['kode'] ?? '');
$data = ambil_pesanan($kode);
$pesanan = $data['pesanan'] ?? null;
$item = $data['item'] ?? [];
$pemilik = $pesanan && (int) $pesanan['id_pembeli'] === id_pengguna();

$menu_aktif = '';
if (!$pesanan || (!$pemilik && !adalah_admin())) {
    http_response_code(404);
    $judul = 'Pesanan tidak ditemukan';
    require __DIR__ . '/../includes/header.php';
    echo '<div class="container seksi"><div class="kosong"><p class="mb-2">Pesanan tidak ditemukan.</p>'
        . '<a href="' . BASE_URL . 'public/pesanan_saya.php">Lihat pesananku</a></div></div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$link_konfirmasi = link_wa(NOMOR_WA_TOKO, teks_konfirmasi_wa($pesanan, $item));

$judul = 'Pesanan ' . $kode;
require __DIR__ . '/../includes/header.php';
?>
<div class="kepala-halaman">
    <div class="container">
        <p class="remah"><a href="<?= BASE_URL ?>public/pesanan_saya.php">Pesanan saya</a> / <?= e($kode) ?></p>
        <h1>Pesanan <?= e($kode) ?> <?= label_status($pesanan['status']) ?></h1>
    </div>
</div>

<div class="container seksi-kecil">
    <?php tampil_pesan(); ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="kotak-bayar">
                <?php if ($pesanan['status'] === 'menunggu_bayar' && $pemilik): ?>
                    <?php if (MODE_DEMO): ?>
                        <p class="peringatan-demo"><strong>Ini demo tugas kuliah.</strong> QRIS dan nomor di halaman ini hanya contoh. Jangan transfer uang sungguhan.</p>
                    <?php endif; ?>
                    <?php /* Hitung mundur: sisa detik dikirim lewat atribut data, dihitung oleh main.js */ ?>
                    <p class="hitung-mundur" data-sisa="<?= max(0, batas_bayar($pesanan['tgl_pesanan']) - time()) ?>" data-selesai="<?= e(BASE_URL . 'public/pembayaran.php?kode=' . urlencode($kode)) ?>">
                        Selesaikan pembayaran dalam <strong class="waktu-sisa">--:--</strong>
                    </p>
                    <p class="mb-0">Total yang harus dibayar</p>
                    <p class="total-bayar"><?= rupiah($pesanan['total_bayar']) ?></p>

                    <?php if ($pesanan['metode_bayar'] === 'qris'): ?>
                        <h2 class="h5">Bayar dengan QRIS</h2>
                        <img class="gambar-qris" src="<?= BASE_URL . e(GAMBAR_QRIS) ?>" alt="Kode QRIS toko untuk pesanan <?= e($kode) ?>" width="256" height="256">
                    <?php else: ?>
                        <h2 class="h5">Transfer ke <?= e(EWALLET_NAMA) ?></h2>
                        <dl class="data-produk">
                            <dt>Nomor</dt><dd><strong><?= e(EWALLET_NOMOR) ?></strong></dd>
                            <dt>Atas nama</dt><dd><?= e(EWALLET_ATAS_NAMA) ?></dd>
                        </dl>
                    <?php endif; ?>

                    <ol class="cara-pesan mb-4">
                        <li>Bayar tepat sesuai total di atas.</li>
                        <li>Simpan atau screenshot bukti pembayaran.</li>
                        <li>Tekan tombol di bawah. WhatsApp terbuka dengan rincian pesanan yang sudah terisi, lalu lampirkan foto bukti pembayaran dan kirim.</li>
                    </ol>

                    <form method="post" action="<?= BASE_URL ?>public/konfirmasi.php" class="mb-2">
                        <?= csrf_input() ?>
                        <input type="hidden" name="kode_pesanan" value="<?= e($kode) ?>">
                        <button type="submit" class="btn btn-aksen btn-lg w-100">Sudah Bayar, Konfirmasi via WhatsApp</button>
                    </form>
                    <form method="post" action="<?= BASE_URL ?>public/pesanan_batal.php" data-konfirmasi="Batalkan pesanan <?= e($kode) ?>? Stok akan dikembalikan.">
                        <?= csrf_input() ?>
                        <input type="hidden" name="kode_pesanan" value="<?= e($kode) ?>">
                        <button type="submit" class="btn btn-garis w-100">Batalkan Pesanan</button>
                    </form>

                <?php elseif ($pesanan['status'] === 'menunggu_konfirmasi'): ?>
                    <h2 class="h5">Menunggu konfirmasi admin</h2>
                    <p>Konfirmasi pembayaran sudah dikirim. Admin akan memeriksa bukti pembayaran lalu menyiapkan pesananmu. Pesanan diambil sendiri di toko.</p>
                    <?php if ($pemilik): ?>
                        <p>Kalau WhatsApp tadi belum terkirim, kirim ulang rincian pesanan:</p>
                        <a class="btn btn-biru" href="<?= e($link_konfirmasi) ?>" target="_blank" rel="noopener noreferrer">Buka WhatsApp dan Kirim Ulang</a>
                    <?php endif; ?>

                <?php elseif ($pesanan['status'] === 'diproses'): ?>
                    <h2 class="h5">Pesanan sedang diproses</h2>
                    <p class="mb-0">Pembayaran sudah dikonfirmasi. Toko sedang menyiapkan pesananmu. Ambil sendiri di toko: <?= e(ALAMAT_TOKO) ?>.</p>

                <?php elseif ($pesanan['status'] === 'selesai'): ?>
                    <h2 class="h5">Pesanan selesai</h2>
                    <p class="mb-0">Terima kasih sudah berbelanja di <?= e(NAMA_TOKO) ?>.</p>

                <?php elseif ($pesanan['status'] === 'kedaluwarsa'): ?>
                    <h2 class="h5">Waktu pembayaran habis</h2>
                    <p class="mb-2">Pesanan ini dibatalkan otomatis karena belum dibayar dalam <?= BATAS_BAYAR_MENIT ?> menit. Stoknya sudah dikembalikan. Silakan pesan lagi.</p>
                    <a class="btn btn-biru" href="<?= BASE_URL ?>public/produk.php">Lihat Produk</a>

                <?php else: ?>
                    <h2 class="h5">Pesanan dibatalkan</h2>
                    <p class="mb-2">Pesanan ini tidak dilanjutkan dan stoknya sudah dikembalikan.</p>
                    <a class="btn btn-biru" href="<?= BASE_URL ?>public/produk.php">Lihat Produk</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="ringkasan">
                <h2 class="h5">Rincian pesanan</h2>
                <ul>
                    <?php foreach ($item as $i): ?>
                        <li><span><?= e($i['nama_produk']) ?> x <?= (int) $i['jumlah_beli'] ?></span><span><?= rupiah($i['subtotal']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="total"><span>Total</span><span class="angka"><?= rupiah($pesanan['total_bayar']) ?></span></div>
                <dl class="data-produk mt-3 mb-0">
                    <dt>Dipesan</dt><dd><?= e(tanggal_indo($pesanan['tgl_pesanan'], true)) ?></dd>
                    <dt>Metode</dt><dd><?= e(nama_metode($pesanan['metode_bayar'])) ?></dd>
                    <dt>Pengambil</dt><dd><?= e($pesanan['nama_penerima']) ?></dd>
                    <dt>WhatsApp</dt><dd><?= e($pesanan['no_wa_penerima']) ?></dd>
                    <dt>Pengambilan</dt><dd>Diambil sendiri di toko: <?= e(ALAMAT_TOKO) ?></dd>
                    <?php if (!empty($pesanan['catatan'])): ?><dt>Pesan</dt><dd><?= nl2br(e($pesanan['catatan'])) ?></dd><?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
