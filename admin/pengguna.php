<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $idu = (int) ($_POST['id_pengguna'] ?? 0);
    $pakai = (int) db_satu(
        'SELECT COUNT(*) AS n FROM membeli WHERE id_pembeli = ?',
        'i', [$idu]
    )['n'];
    if ($idu === id_pengguna()) {
        set_pesan('gagal', 'Akun yang sedang dipakai tidak bisa dihapus.');
    } elseif ($pakai > 0) {
        set_pesan('gagal', 'Pengguna ini punya riwayat pesanan, jadi tidak bisa dihapus.');
    } else {
                db_jalankan('DELETE FROM pembeli WHERE id_pembeli = ?', 'i', [$idu]);
        set_pesan('sukses', 'Pengguna dihapus.');
    }
    arahkan(BASE_URL . 'admin/pengguna.php');
}

$daftar = db_semua('SELECT id_pembeli AS id_pengguna, nama, email, no_wa, role, created_at FROM pembeli ORDER BY role, nama');
$judul = 'Pengguna';
$menu_aktif = 'pengguna';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Pengguna</h1><a class="btn btn-aksen" href="pengguna_form.php">Tambah Pengguna</a></div>
<section class="panel">
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Daftar pengguna</caption>
            <thead><tr><th scope="col">Nama</th><th scope="col">Email</th><th scope="col">WhatsApp</th><th scope="col">Peran</th><th scope="col">Terdaftar</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $u): ?>
                <tr>
                    <td><?= e($u['nama']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['no_wa']) ?></td>
                    <td><?= $u['role'] === 'admin' ? 'Admin' : 'Pelanggan' ?></td>
                    <td><?= e(tanggal_indo($u['created_at'])) ?></td>
                    <td><div class="aksi-tabel">
                        <a class="btn btn-garis btn-sm" href="pengguna_form.php?id=<?= (int) $u['id_pengguna'] ?>">Ubah<span class="visually-hidden"> <?= e($u['nama']) ?></span></a>
                        <?php if ((int) $u['id_pengguna'] !== id_pengguna()): ?>
                        <form method="post" data-konfirmasi="Hapus pengguna <?= e($u['nama']) ?>?">
                            <?= csrf_input() ?><input type="hidden" name="id_pengguna" value="<?= (int) $u['id_pengguna'] ?>">
                            <button type="submit" class="btn btn-bahaya btn-sm">Hapus<span class="visually-hidden"> <?= e($u['nama']) ?></span></button>
                        </form>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
