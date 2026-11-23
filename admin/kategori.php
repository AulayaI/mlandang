<?php
// Kelola kategori produk: ganti nama dan unggah gambar kategori untuk beranda.
// Kategori tidak punya tabel sendiri. Kategori adalah kolom "kategori" di tabel produk,
// jadi kategori baru dibuat dengan mengisi kolom Kategori di form produk.
// Gambar kategori disimpan sebagai file kategori-<nama>.jpg di assets/img/tampilan.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $lama = trim($_POST['kategori_lama'] ?? '');
    $baru = trim($_POST['kategori_baru'] ?? '');
    $ada = db_satu('SELECT COUNT(*) AS n FROM produk WHERE kategori = ?', 's', [$lama]);

    if ($lama === '' || (int) $ada['n'] === 0) {
        set_pesan('gagal', 'Kategori tidak ditemukan.');
        arahkan(BASE_URL . 'admin/kategori.php');
    }
    if ($baru === '' || mb_strlen($baru) > 60) {
        set_pesan('gagal', 'Nama kategori wajib diisi, maksimal 60 karakter.');
        arahkan(BASE_URL . 'admin/kategori.php');
    }

    $slug_lama = slug($lama);
    $slug_baru = slug($baru);
    if ($slug_baru === '') {
        set_pesan('gagal', 'Nama kategori harus memuat huruf atau angka.');
        arahkan(BASE_URL . 'admin/kategori.php');
    }

    // Nama baru tidak boleh sama dengan kategori lain (huruf besar/kecil dianggap sama).
    if ($slug_baru !== $slug_lama) {
        $semua = array_column(db_semua('SELECT DISTINCT kategori FROM produk'), 'kategori');
        foreach ($semua as $nama) {
            if (slug($nama) === $slug_baru) {
                set_pesan('gagal', 'Sudah ada kategori bernama ' . $nama . '.');
                arahkan(BASE_URL . 'admin/kategori.php');
            }
        }
    }

    // 1. Gambar baru (kalau ada file dipilih). Disimpan lebih dulu: kalau gagal, tidak ada yang berubah.
    $ada_file = isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($ada_file) {
        if (!simpan_gambar_kategori($_FILES['gambar'], $slug_baru, $galat)) {
            set_pesan('gagal', $galat);
            arahkan(BASE_URL . 'admin/kategori.php');
        }
    }

    // 2. Ganti nama. Semua produk di kategori itu ikut berpindah ke nama baru.
    if ($baru !== $lama) {
        db_jalankan('UPDATE produk SET kategori = ? WHERE kategori = ?', 'ss', [$baru, $lama]);
        // Gambar lama ikut pindah ke nama file baru (kalau tidak mengunggah gambar baru).
        if (!$ada_file && $slug_baru !== $slug_lama) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                $dari = DIR_GAMBAR_TAMPILAN . 'kategori-' . $slug_lama . '.' . $ext;
                if (is_file($dari)) {
                    rename($dari, DIR_GAMBAR_TAMPILAN . 'kategori-' . $slug_baru . '.' . $ext);
                }
            }
        } elseif ($ada_file && $slug_baru !== $slug_lama) {
            hapus_gambar_kategori($slug_lama);
        }
    }

    set_pesan('sukses', 'Kategori ' . $baru . ' disimpan.');
    arahkan(BASE_URL . 'admin/kategori.php');
}

$daftar = db_semua(
    "SELECT kategori AS nama_kategori, COUNT(*) AS jumlah, SUM(status = 'aktif') AS aktif
     FROM produk
     GROUP BY kategori
     ORDER BY kategori"
);

$judul = 'Kategori';
$menu_aktif = 'kategori';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman">
    <h1>Kategori</h1>
    <a class="btn btn-aksen" href="<?= BASE_URL ?>admin/produk_form.php">Tambah Produk</a>
</div>

<div class="alert alert-info">
    Kategori baru dibuat lewat form produk: isi kolom <strong>Kategori</strong> dengan nama baru. Setelah produknya aktif,
    kategori muncul di sini dan otomatis tampil di beranda. Gambar kategori dipilih di sini.
    Ukuran yang disarankan 4:3 (misalnya 800 x 600 px), JPG, PNG, atau WEBP, maksimal 2 MB.
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Daftar kategori produk</caption>
            <thead><tr><th scope="col">Gambar</th><th scope="col">Kategori</th><th scope="col" class="angka">Produk</th><th scope="col">Di beranda</th><th scope="col">Ubah nama dan gambar</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori. Tambahkan produk dulu.</td></tr><?php endif; ?>
            <?php foreach ($daftar as $i => $k):
                $url = url_gambar_tampilan('kategori-' . slug($k['nama_kategori'])); ?>
                <tr>
                    <td>
                        <?php if ($url !== ''): ?>
                            <img class="foto-tabel" src="<?= e($url) ?>" alt="Gambar kategori <?= e($k['nama_kategori']) ?>" width="64" height="48">
                        <?php else: ?>
                            <span class="text-muted small">Belum ada</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= e($k['nama_kategori']) ?></strong></td>
                    <td class="angka"><?= (int) $k['jumlah'] ?></td>
                    <td><?= (int) $k['aktif'] > 0 ? 'Tampil' : 'Belum tampil (tidak ada produk aktif)' ?></td>
                    <td>
                        <form method="post" enctype="multipart/form-data" class="form-kategori">
                            <?= csrf_input() ?>
                            <input type="hidden" name="kategori_lama" value="<?= e($k['nama_kategori']) ?>">
                            <div>
                                <label for="nama-<?= $i ?>" class="form-label mb-1">Nama kategori</label>
                                <input type="text" class="form-control" id="nama-<?= $i ?>" name="kategori_baru" value="<?= e($k['nama_kategori']) ?>" maxlength="60" required>
                            </div>
                            <div>
                                <label for="gambar-<?= $i ?>" class="form-label mb-1">Gambar baru</label>
                                <input type="file" class="form-control" id="gambar-<?= $i ?>" name="gambar" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <button type="submit" class="btn btn-biru btn-sm">Simpan</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
