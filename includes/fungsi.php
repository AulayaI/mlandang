<?php
// Fungsi bantu kecil yang dipakai di banyak halaman.

// Semua teks dari database atau dari pengguna dilewatkan ke sini sebelum
// dicetak ke HTML, supaya tanda < dan > tidak dibaca browser sebagai kode (XSS).
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

// Untuk label grafik yang sempit: 1250000 menjadi "1,3 jt".
function rupiah_singkat($angka)
{
    $angka = (float) $angka;
    if ($angka >= 1000000) {
        return number_format($angka / 1000000, 1, ',', '.') . ' jt';
    }
    if ($angka >= 1000) {
        return number_format($angka / 1000, 0, ',', '.') . ' rb';
    }
    return number_format($angka, 0, ',', '.');
}

function tanggal_indo($waktu, $dengan_jam = false)
{
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($waktu);
    $hasil = date('j', $t) . ' ' . $bulan[date('n', $t) - 1] . ' ' . date('Y', $t);
    if ($dengan_jam) {
        $hasil .= ', ' . date('H.i', $t);
    }
    return $hasil;
}

// Nomor WA pembeli ditulis bebas (0812..., +62 812..., 62812...).
// wa.me hanya menerima angka berawalan kode negara, jadi diseragamkan di sini.
function nomor_wa($nomor)
{
    $angka = preg_replace('/\D/', '', (string) $nomor);
    if (strpos($angka, '0') === 0) {
        $angka = '62' . substr($angka, 1);
    }
    return $angka;
}

function nomor_wa_valid($nomor)
{
    $angka = preg_replace('/[\s\-+]/', '', (string) $nomor);
    return preg_match('/^(62|0)[0-9]{8,13}$/', $angka) === 1;
}

function link_wa($nomor, $teks = '')
{
    $url = 'https://wa.me/' . nomor_wa($nomor);
    if ($teks !== '') {
        $url .= '?text=' . rawurlencode($teks);
    }
    return $url;
}

function arahkan($tujuan)
{
    header('Location: ' . $tujuan);
    exit;
}

// Alamat tujuan dari parameter URL hanya dipakai kalau masih di dalam situs ini.
// Tanpa pengecekan ini, orang bisa membuat tautan login yang melempar korban ke situs lain.
function url_aman($url, $cadangan)
{
    if (is_string($url) && strpos($url, BASE_URL) === 0 && strpos($url, '//') === false && strpos($url, "\n") === false) {
        return $url;
    }
    return $cadangan;
}

// Pesan sekali tampil (berhasil atau gagal) yang bertahan melewati redirect.
function set_pesan($jenis, $teks)
{
    $_SESSION['pesan'] = ['jenis' => $jenis, 'teks' => $teks];
}

function tampil_pesan()
{
    if (empty($_SESSION['pesan'])) {
        return;
    }
    $p = $_SESSION['pesan'];
    unset($_SESSION['pesan']);
    $kelas = $p['jenis'] === 'sukses' ? 'alert-success' : 'alert-danger';
    echo '<div class="alert ' . $kelas . '" role="alert">' . e($p['teks']) . '</div>';
}

// Token CSRF: form berisi kode rahasia milik session. Tanpa ini, situs lain bisa
// membuat browser admin yang sedang login mengirim perintah hapus tanpa disadari.
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Dipanggil di awal setiap halaman yang menerima form POST.
function wajib_post()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        arahkan(BASE_URL . 'public/index.php');
    }
    $kirim = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) $kirim)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman, lalu coba lagi.');
    }
}

// Satu tempat untuk tampilan status pesanan, dipakai pembeli dan admin.
function label_status($status)
{
    $daftar = [
        'menunggu_bayar'      => ['Menunggu pembayaran', 'status-tunggu'],
        'menunggu_konfirmasi' => ['Menunggu konfirmasi', 'status-konfirmasi'],
        'diproses'            => ['Diproses', 'status-proses'],
        'selesai'             => ['Selesai', 'status-selesai'],
        'batal'               => ['Dibatalkan', 'status-batal'],
        'kedaluwarsa'         => ['Kedaluwarsa', 'status-batal'],
    ];
    $item = $daftar[$status] ?? [$status, 'status-tunggu'];
    return '<span class="status ' . $item[1] . '">' . e($item[0]) . '</span>';
}

function nama_status($status)
{
    $nama = [
        'menunggu_bayar' => 'Menunggu pembayaran', 'menunggu_konfirmasi' => 'Menunggu konfirmasi',
        'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan', 'kedaluwarsa' => 'Kedaluwarsa',
    ];
    return $nama[$status] ?? $status;
}

function nama_metode($metode)
{
    return $metode === 'qris' ? 'QRIS' : 'Transfer ' . EWALLET_NAMA;
}

function foto_produk($nama_file)
{
    if ($nama_file === null || $nama_file === '') {
        return URL_FOTO_PRODUK . 'kosong.svg';
    }
    return URL_FOTO_PRODUK . rawurlencode($nama_file);
}

// Menyimpan gambar produk yang diunggah. Mengembalikan nama file baru,
// atau '' kalau tidak ada file atau gagal (alasannya ada di $galat).
function simpan_gambar_produk($berkas, &$galat)
{
    $galat = '';
    if (!isset($berkas['error']) || $berkas['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($berkas['error'] !== UPLOAD_ERR_OK) {
        $galat = 'Gambar gagal diunggah. Coba lagi.';
        return '';
    }
    if ($berkas['size'] > 2 * 1024 * 1024) {
        $galat = 'Ukuran gambar maksimal 2 MB.';
        return '';
    }
    // Jenis file dibaca dari isinya, bukan dari nama, karena nama mudah dipalsukan.
    $info = @getimagesize($berkas['tmp_name']);
    $boleh = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($boleh[$info[2]])) {
        $galat = 'Format gambar harus JPG, PNG, atau WEBP.';
        return '';
    }
    $nama = 'produk-' . bin2hex(random_bytes(6)) . '.' . $boleh[$info[2]];
    if (!move_uploaded_file($berkas['tmp_name'], DIR_FOTO_PRODUK . $nama)) {
        $galat = 'Gambar tidak bisa disimpan. Periksa izin tulis folder assets/img/produk.';
        return '';
    }
    return $nama;
}

// Menghapus file gambar lama. Basename dipakai supaya nama file dari database
// tidak bisa menunjuk ke folder lain.
function hapus_gambar_produk($nama_file)
{
    if ($nama_file === null || $nama_file === '') {
        return;
    }
    $jalur = DIR_FOTO_PRODUK . basename($nama_file);
    if (is_file($jalur) && strpos(basename($nama_file), 'produk-') === 0) {
        unlink($jalur);
    }
}


// Menyimpan gambar kategori ke assets/img/tampilan/kategori-<slug>.<ext>, supaya langsung
// dikenali beranda (lihat gambar_slot di includes/komponen.php). Gambar lama kategori itu dihapus.
// Mengembalikan true kalau berhasil. Kalau gagal, alasannya ada di $galat.
function simpan_gambar_kategori($berkas, $slug_kategori, &$galat)
{
    $galat = '';
    if ($berkas['error'] !== UPLOAD_ERR_OK) {
        $galat = 'Gambar gagal diunggah. Coba lagi.';
        return false;
    }
    if ($berkas['size'] > 2 * 1024 * 1024) {
        $galat = 'Ukuran gambar maksimal 2 MB.';
        return false;
    }
    $info = @getimagesize($berkas['tmp_name']);
    $boleh = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($boleh[$info[2]])) {
        $galat = 'Format gambar harus JPG, PNG, atau WEBP.';
        return false;
    }
    hapus_gambar_kategori($slug_kategori);
    $tujuan = DIR_GAMBAR_TAMPILAN . 'kategori-' . $slug_kategori . '.' . $boleh[$info[2]];
    if (!move_uploaded_file($berkas['tmp_name'], $tujuan)) {
        $galat = 'Gambar tidak bisa disimpan. Periksa izin tulis folder assets/img/tampilan.';
        return false;
    }
    return true;
}

// Menghapus semua gambar kategori dengan slug tertentu (jpg, jpeg, png, webp).
function hapus_gambar_kategori($slug_kategori)
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $jalur = DIR_GAMBAR_TAMPILAN . 'kategori-' . $slug_kategori . '.' . $ext;
        if (is_file($jalur)) {
            unlink($jalur);
        }
    }
}
