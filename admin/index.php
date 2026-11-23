<?php
// Dashboard admin: ringkasan penjualan, stok, dan pesanan yang perlu ditangani.
require_once __DIR__ . '/../includes/awal.php';
require_once __DIR__ . '/../includes/grafik.php';
wajib_admin();

$hari_ini = date('Y-m-d');
$awal_bulan = date('Y-m-01') . ' 00:00:00';

// Pendapatan hanya dihitung dari pesanan yang sudah dikonfirmasi (diproses atau selesai).
$pendapatan_bulan = (float) db_satu(
    "SELECT COALESCE(SUM(jumlah * harga_satuan), 0) AS n FROM membeli WHERE status IN ('diproses','selesai') AND tgl_pesanan >= ?",
    's', [$awal_bulan]
)['n'];
$pesanan_hari_ini = (int) db_satu(
    "SELECT COUNT(DISTINCT kode_pesanan) AS n FROM membeli WHERE tgl_pesanan >= ?", 's', [$hari_ini . ' 00:00:00']
)['n'];
$perlu_tindak = (int) db_satu("SELECT COUNT(DISTINCT kode_pesanan) AS n FROM membeli WHERE status = 'menunggu_konfirmasi'")['n'];
$total_produk = (int) db_satu("SELECT COUNT(*) AS n FROM produk WHERE status = 'aktif'")['n'];

// Pendapatan 7 hari terakhir. Query mengambil barisnya, pengelompokan per hari dilakukan di PHP.
$label7 = [];
$nilai7 = [];
$tip7 = [];
$peta = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i day"));
    $peta[$tgl] = count($label7);
    $label7[] = date('d/m', strtotime($tgl));
    $nilai7[] = 0;
}
$baris = db_semua(
    "SELECT tgl_pesanan, jumlah * harga_satuan AS total_bayar FROM membeli WHERE status IN ('diproses','selesai') AND tgl_pesanan >= ?",
    's', [date('Y-m-d', strtotime('-6 day')) . ' 00:00:00']
);
foreach ($baris as $b) {
    $k = substr($b['tgl_pesanan'], 0, 10);
    if (isset($peta[$k])) {
        $nilai7[$peta[$k]] += $b['total_bayar'];
    }
}
foreach ($label7 as $i => $l) {
    $tip7[] = $l . ': ' . rupiah($nilai7[$i]);
}

// Stok masuk (transaksi supplier) dan stok keluar (pesanan tidak batal) per minggu, 4 minggu terakhir.
$label_mgg = [];
$masuk = [0, 0, 0, 0];
$keluar = [0, 0, 0, 0];
$tip_mgg = [];
$mulai_ts = strtotime('-27 day', strtotime($hari_ini));
for ($i = 0; $i < 4; $i++) {
    $label_mgg[] = date('d/m', $mulai_ts + $i * 7 * 86400);
}
$mulai = date('Y-m-d', $mulai_ts) . ' 00:00:00';
$minggu_ke = function ($waktu) use ($mulai_ts) {
    $hari = (int) floor((strtotime(substr($waktu, 0, 10)) - $mulai_ts) / 86400);
    return $hari < 0 ? -1 : min(3, intdiv($hari, 7));
};
foreach (db_semua(
    'SELECT tgl_transaksi AS tgl, jumlah AS jml FROM transaksi WHERE tgl_transaksi >= ?',
    's', [$mulai]
) as $b) {
    $m = $minggu_ke($b['tgl']);
    if ($m >= 0) {
        $masuk[$m] += $b['jml'];
    }
}
foreach (db_semua(
    "SELECT tgl_pesanan AS tgl, jumlah AS jml FROM membeli WHERE status NOT IN ('batal', 'kedaluwarsa') AND tgl_pesanan >= ?",
    's', [$mulai]
) as $b) {
    $m = $minggu_ke($b['tgl']);
    if ($m >= 0) {
        $keluar[$m] += $b['jml'];
    }
}
foreach ($label_mgg as $i => $l) {
    $tip_mgg[] = 'Minggu mulai ' . $l . ': masuk ' . $masuk[$i] . ', keluar ' . $keluar[$i];
}

$terlaris = db_semua(
    "SELECT pr.nama_produk, SUM(m.jumlah) AS terjual
     FROM membeli m
     JOIN produk pr ON pr.id_produk = m.id_produk
     WHERE m.status NOT IN ('batal', 'kedaluwarsa')
     GROUP BY pr.id_produk, pr.nama_produk
     ORDER BY terjual DESC
     LIMIT 5"
);

$menipis = db_semua(
    "SELECT id_produk, nama_produk, stok FROM produk WHERE status = 'aktif' AND stok <= ? ORDER BY stok ASC, nama_produk",
    'i', [BATAS_STOK_MENIPIS]
);
$hampir_kadaluarsa = db_semua(
    "SELECT id_produk, nama_produk, tgl_kadaluarsa, stok FROM produk
     WHERE status = 'aktif' AND tgl_kadaluarsa IS NOT NULL AND tgl_kadaluarsa <= ?
     ORDER BY tgl_kadaluarsa ASC",
    's', [date('Y-m-d', strtotime('+30 day'))]
);
$terbaru = db_semua(
    'SELECT kode_pesanan, MIN(tgl_pesanan) AS tgl_pesanan, MIN(nama_penerima) AS nama_penerima,
            SUM(jumlah * harga_satuan) AS total_bayar, MIN(status) AS status, MAX(id_membeli) AS urutan
     FROM membeli GROUP BY kode_pesanan ORDER BY urutan DESC LIMIT 6'
);

$judul = 'Dashboard';
$menu_aktif = 'dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Dashboard</h1><span class="text-muted"><?= e(tanggal_indo($hari_ini)) ?></span></div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="perlu-tindak">
            <span>Menunggu konfirmasi pembayaran</span>
            <span class="besar"><?= $perlu_tindak ?></span>
            <a href="<?= BASE_URL ?>admin/pesanan.php?status=menunggu_konfirmasi">Periksa pesanan</a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6"><div class="statistik"><span class="label">Pendapatan bulan ini</span><span class="nilai"><?= rupiah($pendapatan_bulan) ?></span></div></div>
    <div class="col-lg-3 col-md-6"><div class="statistik"><span class="label">Pesanan hari ini</span><span class="nilai"><?= $pesanan_hari_ini ?></span></div></div>
    <div class="col-lg-3 col-md-6"><div class="statistik"><span class="label">Produk aktif</span><span class="nilai"><?= $total_produk ?></span></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6"><section class="panel h-100"><h2>Pendapatan 7 hari terakhir</h2>
        <?php grafik_kolom($label7, $nilai7, 'rupiah_singkat', $tip7, 'Grafik pendapatan harian 7 hari terakhir'); ?>
    </section></div>
    <div class="col-lg-6"><section class="panel h-100"><h2>Stok masuk dan keluar, 4 minggu terakhir</h2>
        <?php grafik_ganda($label_mgg, $masuk, $keluar, 'Stok masuk', 'Stok keluar', $tip_mgg, 'Perbandingan stok masuk dan keluar per minggu'); ?>
    </section></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4"><section class="panel h-100"><h2>Produk terlaris</h2>
        <?php grafik_mendatar(array_column($terlaris, 'nama_produk'), array_column($terlaris, 'terjual'), 'pcs'); ?>
    </section></div>
    <div class="col-lg-4"><section class="panel h-100"><h2>Stok menipis (&le; <?= BATAS_STOK_MENIPIS ?>)</h2>
        <?php if (!$menipis): ?><p class="kosong mb-0">Semua stok aman.</p><?php else: ?>
        <ul class="list-unstyled mb-0">
            <?php foreach ($menipis as $m): ?>
                <li class="d-flex justify-content-between gap-2 py-1">
                    <a href="<?= BASE_URL ?>admin/produk_form.php?id=<?= (int) $m['id_produk'] ?>"><?= e($m['nama_produk']) ?></a>
                    <strong><?= (int) $m['stok'] === 0 ? 'Habis' : (int) $m['stok'] . ' pcs' ?></strong>
                </li>
            <?php endforeach; ?>
        </ul><?php endif; ?>
    </section></div>
    <div class="col-lg-4"><section class="panel h-100"><h2>Hampir kedaluwarsa (30 hari)</h2>
        <?php if (!$hampir_kadaluarsa): ?><p class="kosong mb-0">Tidak ada.</p><?php else: ?>
        <ul class="list-unstyled mb-0">
            <?php foreach ($hampir_kadaluarsa as $m): ?>
                <li class="d-flex justify-content-between gap-2 py-1">
                    <a href="<?= BASE_URL ?>admin/produk_form.php?id=<?= (int) $m['id_produk'] ?>"><?= e($m['nama_produk']) ?></a>
                    <span><?= e(tanggal_indo($m['tgl_kadaluarsa'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul><?php endif; ?>
    </section></div>
</div>

<section class="panel">
    <h2>Pesanan terbaru</h2>
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Enam pesanan terbaru</caption>
            <thead><tr><th scope="col">No.</th><th scope="col">Tanggal</th><th scope="col">Penerima</th><th scope="col" class="angka">Total</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
            <?php foreach ($terbaru as $p): ?>
                <tr>
                    <td><?= e($p['kode_pesanan']) ?></td>
                    <td><?= e(tanggal_indo($p['tgl_pesanan'], true)) ?></td>
                    <td><?= e($p['nama_penerima']) ?></td>
                    <td class="angka"><?= rupiah($p['total_bayar']) ?></td>
                    <td><?= label_status($p['status']) ?></td>
                    <td><a class="btn btn-garis btn-sm" href="<?= BASE_URL ?>admin/pesanan_detail.php?kode=<?= urlencode($p['kode_pesanan']) ?>">Buka<span class="visually-hidden"> <?= e($p['kode_pesanan']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
