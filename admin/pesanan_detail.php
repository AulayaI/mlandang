<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$kode = trim($_GET['kode'] ?? $_POST['kode_pesanan'] ?? '');
$data = ambil_pesanan($kode);
if (!$data) {
    set_pesan('gagal', 'Pesanan tidak ditemukan.');
    arahkan(BASE_URL . 'admin/pesanan.php');
}
$pesanan = $data['pesanan'];
$item = $data['item'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $galat = ubah_status_pesanan($kode, $_POST['status_baru'] ?? '');
    if ($galat === '') {
        set_pesan('sukses', 'Status pesanan diperbarui.');
    } else {
        set_pesan('gagal', $galat);
    }
    arahkan(BASE_URL . 'admin/pesanan_detail.php?kode=' . urlencode($kode));
}

$pilihan = status_berikutnya($pesanan['status']);
$pesan_wa = 'Halo ' . $pesanan['nama_penerima'] . ', pesanan ' . $kode . ' di ' . NAMA_TOKO . ' statusnya sekarang: ' . nama_status($pesanan['status']) . '.';

$judul = 'Pesanan ' . $kode;
$menu_aktif = 'pesanan';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Pesanan <?= e($kode) ?> <?= label_status($pesanan['status']) ?></h1><a class="btn btn-garis" href="pesanan.php">Kembali</a></div>
<div class="row g-3">
    <div class="col-lg-7">
        <section class="panel">
            <h2>Barang</h2>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">Barang pada pesanan</caption>
                    <thead><tr><th scope="col">Produk</th><th scope="col" class="angka">Jumlah</th><th scope="col" class="angka">Harga</th><th scope="col" class="angka">Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach ($item as $i): ?>
                        <tr><td><?= e($i['nama_produk']) ?></td><td class="angka"><?= (int) $i['jumlah_beli'] ?></td><td class="angka"><?= rupiah($i['harga_satuan']) ?></td><td class="angka"><?= rupiah($i['subtotal']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><th scope="row" colspan="3" class="angka">Total</th><td class="angka"><strong><?= rupiah($pesanan['total_bayar']) ?></strong></td></tr></tfoot>
                </table>
            </div>
        </section>
    </div>
    <div class="col-lg-5">
        <section class="panel mb-3">
            <h2>Pengambil</h2>
            <dl class="data-produk mb-3">
                <dt>Dipesan</dt><dd><?= e(tanggal_indo($pesanan['tgl_pesanan'], true)) ?></dd>
                <dt>Nama</dt><dd><?= e($pesanan['nama_penerima']) ?></dd>
                <dt>Email</dt><dd><?= e($pesanan['email_penerima']) ?></dd>
                <dt>WhatsApp</dt><dd><?= e($pesanan['no_wa_penerima']) ?></dd>
                <dt>Pengambilan</dt><dd>Diambil sendiri di toko</dd>
                <dt>Metode</dt><dd><?= e(nama_metode($pesanan['metode_bayar'])) ?></dd>
                <?php if (!empty($pesanan['catatan'])): ?><dt>Pesan</dt><dd><?= nl2br(e($pesanan['catatan'])) ?></dd><?php endif; ?>
            </dl>
            <a class="btn btn-biru" href="<?= e(link_wa($pesanan['no_wa_penerima'], $pesan_wa)) ?>" target="_blank" rel="noopener noreferrer">Hubungi Pembeli via WhatsApp</a>
        </section>
        <section class="panel">
            <h2>Ubah status</h2>
            <?php if (!$pilihan): ?>
                <p class="mb-0">Pesanan ini sudah berstatus akhir dan tidak bisa diubah.</p>
            <?php else: ?>
                <p>Periksa bukti pembayaran di WhatsApp sebelum menandai pesanan diproses.</p>
                <div class="aksi-tabel">
                    <?php foreach ($pilihan as $s): ?>
                        <form method="post" action=""<?= $s === 'batal' ? ' data-konfirmasi="Batalkan pesanan ' . e($kode) . '? Stok akan dikembalikan."' : '' ?>>
                            <?= csrf_input() ?>
                            <input type="hidden" name="kode_pesanan" value="<?= e($kode) ?>">
                            <input type="hidden" name="status_baru" value="<?= e($s) ?>">
                            <button type="submit" class="btn <?= $s === 'batal' ? 'btn-bahaya' : 'btn-aksen' ?>"><?= $s === 'batal' ? 'Batalkan pesanan' : 'Tandai ' . e(strtolower(nama_status($s))) ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
