<?php
// Katalog produk: filter kategori, pencarian, urutan, dan halaman.
// Bisa dilihat tanpa login. Tombol pesan baru meminta login (lihat kartu_produk).
require_once __DIR__ . '/../includes/awal.php';

// Kategori adalah kolom di tabel produk, jadi filternya mencocokkan teks kategori.
$kat = trim($_GET['kategori'] ?? '');
$cari = trim($_GET['q'] ?? '');
$urut = $_GET['urut'] ?? 'terbaru';
$halaman = max(1, (int) ($_GET['hal'] ?? 1));
$per_halaman = 12;

// Urutan hanya boleh dari daftar ini. Nama kolom tidak bisa dipasang lewat tanda tanya,
// jadi pilihan pengguna dicocokkan dulu dengan daftar yang kita tulis sendiri.
$urutan_sql = [
    'terbaru'  => 'p.id_produk DESC',
    'termurah' => 'p.harga ASC, p.id_produk DESC',
    'termahal' => 'p.harga DESC, p.id_produk DESC',
    'nama'     => 'p.nama_produk ASC',
    'kategori' => 'p.kategori ASC, p.nama_produk ASC',
];
if (!isset($urutan_sql[$urut])) {
    $urut = 'terbaru';
}

$syarat = ["p.status = 'aktif'"];
$tipe = '';
$param = [];
if ($kat !== '') {
    $syarat[] = 'p.kategori = ?';
    $tipe .= 's';
    $param[] = $kat;
}
if ($cari !== '') {
    $syarat[] = '(p.nama_produk LIKE ? OR p.merk LIKE ?)';
    $tipe .= 'ss';
    $param[] = '%' . $cari . '%';
    $param[] = '%' . $cari . '%';
}
$where = implode(' AND ', $syarat);

$total = (int) db_satu("SELECT COUNT(*) AS n FROM produk p WHERE $where", $tipe, $param)['n'];
$jumlah_halaman = max(1, (int) ceil($total / $per_halaman));
$halaman = min($halaman, $jumlah_halaman);

$produk = db_semua(
    "SELECT p.*, p.kategori AS nama_kategori
     FROM produk p
     WHERE $where
     ORDER BY {$urutan_sql[$urut]}
     LIMIT ? OFFSET ?",
    $tipe . 'ii',
    array_merge($param, [$per_halaman, ($halaman - 1) * $per_halaman])
);

$kategori = db_semua("SELECT DISTINCT kategori AS nama_kategori FROM produk WHERE status = 'aktif' ORDER BY kategori");

// Membuat alamat katalog dengan filter yang sedang aktif, ditambah perubahan tertentu.
$buat_url = function ($tambah) use ($kat, $cari, $urut) {
    $dasar = [
        'kategori' => $kat !== '' ? $kat : null,
        'q'        => $cari !== '' ? $cari : null,
        'urut'     => $urut !== 'terbaru' ? $urut : null,
    ];
    $gabung = array_filter($tambah + $dasar, function ($v) { return $v !== null; });
    return BASE_URL . 'public/produk.php' . ($gabung ? '?' . http_build_query($gabung) : '');
};

$nama_kategori_aktif = '';
foreach ($kategori as $k) {
    if ($k['nama_kategori'] === $kat) {
        $nama_kategori_aktif = $k['nama_kategori'];
    }
}

$judul = $nama_kategori_aktif !== '' ? $nama_kategori_aktif : 'Semua Produk';
$menu_aktif = 'produk';
require __DIR__ . '/../includes/header.php';
?>
<div class="kepala-halaman">
    <div class="container">
        <p class="remah"><a href="<?= BASE_URL ?>public/index.php">Beranda</a> / Produk</p>
        <h1><?= e($judul) ?></h1>
    </div>
</div>

<div class="container seksi-kecil">
    <?php tampil_pesan(); ?>

    <form class="row g-2 align-items-end mb-3" method="get" action="<?= BASE_URL ?>public/produk.php">
        <?php if ($kat !== ''): ?><input type="hidden" name="kategori" value="<?= e($kat) ?>"><?php endif; ?>
        <div class="col-12 col-md-6">
            <label for="cari" class="form-label">Cari produk atau merk</label>
            <input type="search" class="form-control" id="cari" name="q" value="<?= e($cari) ?>" maxlength="60" placeholder="Contoh: nugget">
        </div>
        <div class="col-8 col-md-3">
            <label for="urut" class="form-label">Urutkan</label>
            <select class="form-select" id="urut" name="urut">
                <option value="terbaru"<?= $urut === 'terbaru' ? ' selected' : '' ?>>Terbaru</option>
                <option value="termurah"<?= $urut === 'termurah' ? ' selected' : '' ?>>Harga terendah</option>
                <option value="termahal"<?= $urut === 'termahal' ? ' selected' : '' ?>>Harga tertinggi</option>
                <option value="nama"<?= $urut === 'nama' ? ' selected' : '' ?>>Nama A sampai Z</option>
                <option value="kategori"<?= $urut === 'kategori' ? ' selected' : '' ?>>Kategori</option>
            </select>
        </div>
        <div class="col-4 col-md-3">
            <button type="submit" class="btn btn-biru w-100">Cari</button>
        </div>
    </form>

    <nav aria-label="Filter kategori" class="mb-4">
        <ul class="filter-kategori">
            <li><a href="<?= e(BASE_URL . 'public/produk.php' . ($cari !== '' ? '?q=' . urlencode($cari) : '')) ?>"<?= $kat === '' ? ' aria-current="true"' : '' ?>>Semua</a></li>
            <?php foreach ($kategori as $k): ?>
                <li><a href="<?= e($buat_url(['kategori' => $k['nama_kategori'], 'hal' => null])) ?>"<?= $k['nama_kategori'] === $kat ? ' aria-current="true"' : '' ?>><?= e($k['nama_kategori']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <p class="text-muted" role="status"><?= $total ?> produk ditemukan</p>

    <?php if ($produk): ?>
        <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-3 row-cols-xl-4">
            <?php foreach ($produk as $p): ?>
                <div class="col"><?php kartu_produk($p); ?></div>
            <?php endforeach; ?>
        </div>

        <?php if ($jumlah_halaman > 1): ?>
            <nav class="d-flex justify-content-between align-items-center mt-4" aria-label="Halaman produk">
                <?php if ($halaman > 1): ?>
                    <a class="btn btn-garis" href="<?= e($buat_url(['hal' => $halaman - 1])) ?>">Halaman sebelumnya</a>
                <?php else: ?><span></span><?php endif; ?>
                <span>Halaman <?= $halaman ?> dari <?= $jumlah_halaman ?></span>
                <?php if ($halaman < $jumlah_halaman): ?>
                    <a class="btn btn-garis" href="<?= e($buat_url(['hal' => $halaman + 1])) ?>">Halaman berikutnya</a>
                <?php else: ?><span></span><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="kosong">
            <p class="mb-2">Tidak ada produk yang cocok dengan pencarianmu.</p>
            <a href="<?= BASE_URL ?>public/produk.php">Tampilkan semua produk</a>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
