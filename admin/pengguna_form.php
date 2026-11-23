<?php
// Admin menambah pengguna (admin atau pelanggan) dan mengubah datanya.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$id = (int) ($_GET['id'] ?? 0);
$lama = $id ? db_satu('SELECT id_pembeli AS id_pengguna, nama, email, no_wa, role FROM pembeli WHERE id_pembeli = ?', 'i', [$id]) : null;
if ($id && !$lama) {
    set_pesan('gagal', 'Pengguna tidak ditemukan.');
    arahkan(BASE_URL . 'admin/pengguna.php');
}
$nilai = $lama ?: ['nama' => '', 'email' => '', 'no_wa' => '', 'role' => 'pelanggan'];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    foreach (['nama', 'email', 'no_wa'] as $k) {
        $nilai[$k] = trim($_POST[$k] ?? '');
    }
    $nilai['role'] = $_POST['role'] ?? 'pelanggan';
    $password = $_POST['password'] ?? '';

    if (mb_strlen($nilai['nama']) < 3 || mb_strlen($nilai['nama']) > 100) {
        $galat['nama'] = 'Nama 3 sampai 100 karakter.';
    }
    if (!filter_var($nilai['email'], FILTER_VALIDATE_EMAIL) || strlen($nilai['email']) > 100) {
        $galat['email'] = 'Format email belum benar.';
    } elseif (db_satu('SELECT 1 AS ada FROM pembeli WHERE email = ? AND id_pembeli <> ?', 'si', [$nilai['email'], $id])) {
        $galat['email'] = 'Email sudah dipakai pengguna lain.';
    }
    if (!nomor_wa_valid($nilai['no_wa'])) {
        $galat['no_wa'] = 'Nomor WhatsApp berupa angka, contoh: 081234567890.';
    }
    if (!in_array($nilai['role'], ['admin', 'pelanggan'], true)) {
        $galat['role'] = 'Peran tidak valid.';
    }
    // Admin tidak boleh menurunkan perannya sendiri, supaya tidak mengunci dirinya dari dashboard.
    if ($lama && $id === id_pengguna() && $nilai['role'] !== 'admin') {
        $galat['role'] = 'Peran akun yang sedang dipakai tidak bisa diturunkan.';
    }
    if ((!$lama || $password !== '') && strlen($password) < 8) {
        $galat['password'] = $lama ? 'Password baru minimal 8 karakter, atau kosongkan.' : 'Password minimal 8 karakter.';
    }

    if (!$galat) {
        if ($lama) {
            db_jalankan('UPDATE pembeli SET nama=?, email=?, no_wa=?, role=? WHERE id_pembeli=?', 'ssssi', [$nilai['nama'], $nilai['email'], $nilai['no_wa'], $nilai['role'], $id]);
            if ($password !== '') {
                db_jalankan('UPDATE pembeli SET password=? WHERE id_pembeli=?', 'si', [password_hash($password, PASSWORD_DEFAULT), $id]);
            }
            if ($id === id_pengguna()) {
                $_SESSION['nama'] = $nilai['nama'];
            }
            set_pesan('sukses', 'Pengguna diperbarui.');
        } else {
            db_jalankan(
                'INSERT INTO pembeli (nama, email, password, no_wa, role, created_at) VALUES (?, ?, ?, ?, ?, ?)',
                'ssssss', [$nilai['nama'], $nilai['email'], password_hash($password, PASSWORD_DEFAULT), $nilai['no_wa'], $nilai['role'], date('Y-m-d H:i:s')]
            );
            set_pesan('sukses', 'Pengguna ditambahkan.');
        }
        arahkan(BASE_URL . 'admin/pengguna.php');
    }
}

$judul = $lama ? 'Ubah Pengguna' : 'Tambah Pengguna';
$menu_aktif = 'pengguna';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1><?= e($judul) ?></h1><a class="btn btn-garis" href="pengguna.php">Kembali</a></div>
<section class="panel">
    <form method="post" action="" data-validasi>
        <?= csrf_input() ?>
        <div class="row">
            <div class="col-md-6"><?php field('nama', 'Nama', 'text', $nilai['nama'], $galat, 'data-wajib data-min="3" maxlength="100"'); ?></div>
            <div class="col-md-6"><?php field('email', 'Email', 'email', $nilai['email'], $galat, 'data-wajib data-tipe="email" maxlength="100"'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php field('no_wa', 'Nomor WhatsApp', 'tel', $nilai['no_wa'], $galat, 'data-wajib data-tipe="telepon" maxlength="20"'); ?></div>
            <div class="col-md-4"><?php field_pilih('role', 'Peran', ['pelanggan' => 'Pelanggan', 'admin' => 'Admin'], $nilai['role'], $galat); ?></div>
            <div class="col-md-4"><?php field('password', $lama ? 'Password baru (kosongkan jika tidak diganti)' : 'Password', 'password', '', $galat, ($lama ? '' : 'data-wajib ') . 'data-min="8" autocomplete="new-password"'); ?></div>
        </div>
        <button type="submit" class="btn btn-aksen">Simpan</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
