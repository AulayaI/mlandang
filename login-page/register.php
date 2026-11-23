<?php
// Pendaftaran akun pembeli. Role selalu 'pelanggan' dan ditetapkan di sini,
// tidak pernah dibaca dari form. Akun admin hanya dibuat oleh admin lewat
// halaman Pengguna di dashboard.
require_once __DIR__ . '/../includes/awal.php';

if (sudah_login()) {
    arahkan(BASE_URL . 'public/index.php');
}

$tujuan = $_GET['lanjut'] ?? $_POST['lanjut'] ?? '';
$nilai = ['nama' => '', 'email' => '', 'no_wa' => ''];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $nilai['nama'] = trim($_POST['nama'] ?? '');
    $nilai['email'] = trim($_POST['email'] ?? '');
    $nilai['no_wa'] = trim($_POST['no_wa'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (mb_strlen($nilai['nama']) < 3 || mb_strlen($nilai['nama']) > 100) {
        $galat['nama'] = 'Nama lengkap 3 sampai 100 karakter.';
    }
    if (!filter_var($nilai['email'], FILTER_VALIDATE_EMAIL) || strlen($nilai['email']) > 100) {
        $galat['email'] = 'Format email belum benar.';
    } elseif (db_satu('SELECT id_pembeli FROM pembeli WHERE email = ?', 's', [$nilai['email']])) {
        $galat['email'] = 'Email ini sudah terdaftar. Silakan masuk.';
    }
    if (!nomor_wa_valid($nilai['no_wa'])) {
        $galat['no_wa'] = 'Nomor WhatsApp berupa angka, contoh: 081234567890.';
    }
    if (strlen($password) < 8) {
        $galat['password'] = 'Password minimal 8 karakter.';
    }
    if ($password !== $konfirmasi) {
        $galat['konfirmasi'] = 'Konfirmasi password belum sama.';
    }

    if (!$galat) {
        db_jalankan(
            "INSERT INTO pembeli (nama, email, password, no_wa, role, created_at) VALUES (?, ?, ?, ?, 'pelanggan', ?)",
            'sssss',
            [$nilai['nama'], $nilai['email'], password_hash($password, PASSWORD_DEFAULT), $nilai['no_wa'], date('Y-m-d H:i:s')]
        );
        $id_baru = db_id_terakhir();

        // Setelah daftar langsung masuk, supaya pembeli tidak perlu mengisi form dua kali.
        session_regenerate_id(true);
        $_SESSION['id_pengguna'] = (int) $id_baru;
        $_SESSION['nama'] = $nilai['nama'];
        $_SESSION['role'] = 'pelanggan';
        set_pesan('sukses', 'Akun berhasil dibuat. Selamat berbelanja.');
        arahkan(url_aman($tujuan, BASE_URL . 'public/produk.php'));
    }
}

$judul = 'Daftar Akun';
$menu_aktif = '';
require __DIR__ . '/../includes/auth_atas.php';
?>
            <h1>Daftar akun pembeli</h1>
            <form method="post" action="" data-validasi>
                <?= csrf_input() ?>
                <input type="hidden" name="lanjut" value="<?= e($tujuan) ?>">
                <?php field('nama', 'Nama lengkap', 'text', $nilai['nama'], $galat, 'data-wajib data-min="3" maxlength="100" autocomplete="name"'); ?>
                <?php field('email', 'Email', 'email', $nilai['email'], $galat, 'data-wajib data-tipe="email" maxlength="100" autocomplete="email"'); ?>
                <?php field('no_wa', 'Nomor WhatsApp', 'tel', $nilai['no_wa'], $galat, 'data-wajib data-tipe="telepon" maxlength="20" autocomplete="tel"'); ?>
                <?php field('password', 'Password', 'password', '', $galat, 'data-wajib data-min="8" autocomplete="new-password"'); ?>
                <?php field('konfirmasi', 'Ulangi password', 'password', '', $galat, 'data-wajib data-sama="#f-password" autocomplete="new-password"'); ?>
                <button type="submit" class="btn btn-biru w-100">Buat Akun</button>
            </form>
            <p class="mt-3 mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>login-page/login.php<?= $tujuan !== '' ? '?lanjut=' . urlencode($tujuan) : '' ?>">Masuk</a></p>
<?php require __DIR__ . '/../includes/auth_bawah.php'; ?>
