<?php
// Satu form login untuk semua orang. Peran (admin atau pelanggan) dibaca dari
// database, bukan dipilih pengguna, supaya tidak ada yang bisa "login sebagai admin"
// hanya dengan mengubah isian form.
require_once __DIR__ . '/../includes/awal.php';

$tujuan = $_GET['lanjut'] ?? $_POST['lanjut'] ?? '';

if (sudah_login()) {
    arahkan(adalah_admin() ? BASE_URL . 'admin/index.php' : BASE_URL . 'public/index.php');
}

$galat = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pengguna = db_satu('SELECT id_pembeli, nama, password, role FROM pembeli WHERE email = ?', 's', [$email]);

    // Pesan error sama untuk email tidak terdaftar dan password salah,
    // supaya orang tidak bisa menebak email mana yang punya akun.
    if ($pengguna && password_verify($password, $pengguna['password'])) {
        // ID session diganti saat login supaya session lama yang mungkin bocor tidak terpakai.
        session_regenerate_id(true);
        $_SESSION['id_pengguna'] = (int) $pengguna['id_pembeli'];
        $_SESSION['nama'] = $pengguna['nama'];
        $_SESSION['role'] = $pengguna['role'];

        $bawaan = $pengguna['role'] === 'admin' ? BASE_URL . 'admin/index.php' : BASE_URL . 'public/index.php';
        arahkan(url_aman($tujuan, $bawaan));
    }
    $galat['umum'] = 'Email atau password salah.';
}

$judul = 'Masuk';
$menu_aktif = '';
require __DIR__ . '/../includes/auth_atas.php';
?>
            <h1>Masuk ke akun</h1>
            <?php tampil_pesan(); ?>
            <?php if (isset($galat['umum'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($galat['umum']) ?></div>
            <?php endif; ?>
            <form method="post" action="" data-validasi>
                <?= csrf_input() ?>
                <input type="hidden" name="lanjut" value="<?= e($tujuan) ?>">
                <?php field('email', 'Email', 'email', $email, [], 'data-wajib data-tipe="email" autocomplete="email"'); ?>
                <?php field('password', 'Password', 'password', '', [], 'data-wajib autocomplete="current-password"'); ?>
                <button type="submit" class="btn btn-biru w-100">Masuk</button>
            </form>
            <p class="mt-3 mb-0">Belum punya akun? <a href="<?= BASE_URL ?>login-page/register.php<?= $tujuan !== '' ? '?lanjut=' . urlencode($tujuan) : '' ?>">Daftar akun pembeli</a></p>
<?php require __DIR__ . '/../includes/auth_bawah.php'; ?>
