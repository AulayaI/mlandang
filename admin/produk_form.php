<?php
// Tambah dan ubah produk dalam satu halaman. ?id=... berarti mode ubah.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$id = (int) ($_GET['id'] ?? 0);
$lama = $id ? db_satu('SELECT * FROM produk WHERE id_produk = ?', 'i', [$id]) : null;
if ($id && !$lama) {
    set_pesan('gagal', 'Produk tidak ditemukan.');
    arahkan(BASE_URL . 'admin/produk.php');
}

// Kategori adalah kolom teks di tabel produk. Pilihan yang sudah dipakai produk lain
// ditawarkan sebagai saran, tapi admin bebas mengetik kategori baru.
$saran_kategori = array_column(db_semua('SELECT DISTINCT kategori FROM produk ORDER BY kategori'), 'kategori');

$nilai = $lama ?: [
    'kategori' => '', 'nama_produk' => '', 'merk' => '', 'berat_gram' => 500,
    'harga' => '', 'stok' => 0, 'tgl_kadaluarsa' => '', 'deskripsi' => '', 'gambar' => '',
    'status' => 'aktif',
];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $nilai['kategori'] = trim($_POST['kategori'] ?? '');
    $nilai['nama_produk'] = trim($_POST['nama_produk'] ?? '');
    $nilai['merk'] = trim($_POST['merk'] ?? '');
    $nilai['berat_gram'] = trim($_POST['berat_gram'] ?? '');
    $nilai['harga'] = trim($_POST['harga'] ?? '');
    $nilai['tgl_kadaluarsa'] = trim($_POST['tgl_kadaluarsa'] ?? '');
    $nilai['deskripsi'] = trim($_POST['deskripsi'] ?? '');
    $nilai['status'] = $_POST['status'] ?? 'aktif';
    // Stok baru hanya diisi saat produk dibuat. Setelah itu stok berubah lewat Pembelian Stok dan pesanan.
    if (!$lama) {
        $nilai['stok'] = trim($_POST['stok'] ?? '0');
    }

    if ($nilai['kategori'] === '' || mb_strlen($nilai['kategori']) > 60) {
        $galat['kategori'] = 'Kategori wajib diisi, maksimal 60 karakter.';
    }
    if (mb_strlen($nilai['nama_produk']) < 3 || mb_strlen($nilai['nama_produk']) > 120) {
        $galat['nama_produk'] = 'Nama produk 3 sampai 120 karakter.';
    }
    if ($nilai['merk'] === '' || mb_strlen($nilai['merk']) > 60) {
        $galat['merk'] = 'Merk wajib diisi, maksimal 60 karakter.';
    }
    if (!ctype_digit((string) $nilai['berat_gram']) || (int) $nilai['berat_gram'] < 1) {
        $galat['berat_gram'] = 'Berat berupa angka gram, minimal 1.';
    }
    if (!ctype_digit((string) $nilai['harga']) || (int) $nilai['harga'] < 1) {
        $galat['harga'] = 'Harga berupa angka rupiah tanpa titik.';
    }
    if (!$lama && !ctype_digit((string) $nilai['stok'])) {
        $galat['stok'] = 'Stok berupa angka, 0 atau lebih.';
    }
    if ($nilai['tgl_kadaluarsa'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $nilai['tgl_kadaluarsa']);
        if (!$d || $d->format('Y-m-d') !== $nilai['tgl_kadaluarsa']) {
            $galat['tgl_kadaluarsa'] = 'Tanggal tidak valid.';
        }
    }
    if (!in_array($nilai['status'], ['aktif', 'nonaktif'], true)) {
        $galat['status'] = 'Status tidak valid.';
    }

    $gambar_baru = simpan_gambar_produk($_FILES['gambar'] ?? [], $galat_gambar);
    if ($galat_gambar !== '') {
        $galat['gambar'] = $galat_gambar;
    }

    if (!$galat) {
        $kadaluarsa = $nilai['tgl_kadaluarsa'] !== '' ? $nilai['tgl_kadaluarsa'] : null;
        $gambar = $gambar_baru !== '' ? $gambar_baru : ($lama['gambar'] ?? null);
        if ($lama) {
            db_jalankan(
                'UPDATE produk SET kategori=?, nama_produk=?, merk=?, berat_gram=?, harga=?, tgl_kadaluarsa=?, deskripsi=?, gambar=?, status=? WHERE id_produk=?',
                'sssidssssi',
                [$nilai['kategori'], $nilai['nama_produk'], $nilai['merk'], (int) $nilai['berat_gram'], (int) $nilai['harga'],
                 $kadaluarsa, $nilai['deskripsi'], $gambar, $nilai['status'], $id]
            );
            if ($gambar_baru !== '') {
                hapus_gambar_produk($lama['gambar']);
            }
            set_pesan('sukses', 'Produk diperbarui.');
        } else {
            db_jalankan(
                'INSERT INTO produk (kategori,nama_produk,merk,berat_gram,harga,stok,tgl_kadaluarsa,deskripsi,gambar,status) VALUES (?,?,?,?,?,?,?,?,?,?)',
                'sssidissss',
                [$nilai['kategori'], $nilai['nama_produk'], $nilai['merk'], (int) $nilai['berat_gram'], (int) $nilai['harga'],
                 (int) $nilai['stok'], $kadaluarsa, $nilai['deskripsi'], $gambar, $nilai['status']]
            );
            set_pesan('sukses', 'Produk ditambahkan.');
        }
        arahkan(BASE_URL . 'admin/produk.php');
    }
}

$judul = $lama ? 'Ubah Produk' : 'Tambah Produk';
$menu_aktif = 'produk';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1><?= e($judul) ?></h1><a class="btn btn-garis" href="produk.php">Kembali</a></div>

<section class="panel">
    <form method="post" action="" enctype="multipart/form-data" data-validasi>
        <?= csrf_input() ?>
        <div class="row">
            <div class="col-md-8"><?php field('nama_produk', 'Nama produk', 'text', $nilai['nama_produk'], $galat, 'data-wajib data-min="3" maxlength="120"'); ?></div>
            <div class="col-md-4"><?php field('merk', 'Merk', 'text', $nilai['merk'], $galat, 'data-wajib maxlength="60"'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?php field('kategori', 'Kategori', 'text', $nilai['kategori'], $galat, 'data-wajib maxlength="60" list="saran-kategori"'); ?>
                <datalist id="saran-kategori"><?php foreach ($saran_kategori as $k): ?><option value="<?= e($k) ?>"><?php endforeach; ?></datalist>
            </div>
            <div class="col-md-4"><?php field('berat_gram', 'Berat (gram)', 'number', $nilai['berat_gram'], $galat, 'data-wajib data-angka-min="1" min="1"'); ?></div>
            <div class="col-md-4"><?php field('harga', 'Harga (Rp)', 'number', $nilai['harga'], $galat, 'data-wajib data-angka-min="1" min="1"'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?php if ($lama): ?>
                    <div class="mb-3"><span class="form-label d-block">Stok sekarang</span><strong><?= (int) $nilai['stok'] ?></strong><br><small class="text-muted">Berubah lewat menu Pembelian Stok dan pesanan.</small></div>
                <?php else: ?>
                    <?php field('stok', 'Stok awal', 'number', $nilai['stok'], $galat, 'data-wajib data-angka-min="0" min="0"'); ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4"><?php field('tgl_kadaluarsa', 'Tanggal kedaluwarsa (boleh kosong)', 'date', $nilai['tgl_kadaluarsa'] ?? '', $galat); ?></div>
            <div class="col-md-4"><?php field_pilih('status', 'Status', ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'], $nilai['status'], $galat); ?></div>
        </div>
        <?php field_area('deskripsi', 'Deskripsi', $nilai['deskripsi'] ?? '', $galat, '', 3); ?>

        <div class="mb-3">
            <label for="f-gambar" class="form-label">Gambar produk (JPG, PNG, WEBP, maksimal 2 MB)</label>
            <input type="file" class="form-control<?= isset($galat['gambar']) ? ' is-invalid' : '' ?>" id="f-gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
            <div class="invalid-feedback"><?= e($galat['gambar'] ?? '') ?></div>
            <?php if (!empty($nilai['gambar'])): ?>
                <div class="mt-2"><img class="foto-tabel" src="<?= e(foto_produk($nilai['gambar'])) ?>" alt="Gambar produk saat ini" width="48" height="48"> <small class="text-muted">Gambar saat ini. Pilih file baru untuk menggantinya.</small></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-aksen">Simpan</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
