<?php
// Potongan tampilan yang dipakai di banyak halaman.

// Ikon memakai library Bootstrap Icons (dimuat di header.php lewat CDN).
// Pemakaian di HTML: <i class="bi bi-cart3"></i>. Daftar ikon: icons.getbootstrap.com
// Fungsi di bawah hanya pembungkus supaya nama ikon di satu tempat.
function ikon($nama, $kelas = '')
{
    return '<i class="bi bi-' . e($nama) . ($kelas !== '' ? ' ' . e($kelas) : '') . '" aria-hidden="true"></i>';
}

function ikon_kristal()
{
    return ikon('snow2', 'kristal');
}

function ikon_chat()
{
    return ikon('whatsapp', 'ikon-chat');
}

// Mengubah "Nugget & Ayam Olahan" menjadi "nugget-ayam-olahan" untuk nama file gambar.
function slug($teks)
{
    $teks = strtolower(trim((string) $teks));
    $teks = preg_replace('/[^a-z0-9]+/', '-', $teks);
    return trim($teks, '-');
}

// Mencari file gambar tampilan dengan nama dasar tertentu. Mengembalikan URL atau ''.
function url_gambar_tampilan($nama)
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $jalur = DIR_GAMBAR_TAMPILAN . $nama . '.' . $ext;
        if (is_file($jalur)) {
            // ?v=waktu-ubah: kalau gambar diganti (nama file sama), browser memuat yang baru.
            return URL_GAMBAR_TAMPILAN . $nama . '.' . $ext . '?v=' . filemtime($jalur);
        }
    }
    return '';
}

// Menampilkan gambar slot. Kalau file belum ada, tampil kotak placeholder
// yang memberi tahu nama file yang harus ditaruh.
function gambar_slot($nama, $alt, $kelas = '', $keterangan = '')
{
    $url = url_gambar_tampilan($nama);
    if ($url !== '') {
        return '<img class="' . e($kelas) . '" src="' . e($url) . '" alt="' . e($alt) . '" loading="lazy">';
    }
    return '<div class="placeholder-gambar ' . e($kelas) . '" role="img" aria-label="Tempat gambar: ' . e($alt) . '">'
        . ikon('image') . '<span>Tempat gambar<br><code>' . e($nama) . '.jpg</code></span>'
        . ($keterangan !== '' ? '<small>' . e($keterangan) . '</small>' : '') . '</div>';
}

// Judul produk yang dapat lencana "Baru" (3 produk aktif terbaru) atau "Terlaris"
// (3 produk dengan penjualan terbanyak). Keduanya dihitung dari data, bukan ditulis manual.
function lencana_produk($id)
{
    static $baru = null, $laris = null;
    if ($baru === null) {
        $baru = array_column(db_semua("SELECT id_produk FROM produk WHERE status = 'aktif' ORDER BY id_produk DESC LIMIT 3"), 'id_produk');
        $laris = array_column(db_semua(
            "SELECT id_produk FROM membeli WHERE status NOT IN ('batal', 'kedaluwarsa') GROUP BY id_produk ORDER BY SUM(jumlah) DESC LIMIT 3"
        ), 'id_produk');
    }
    if (in_array($id, $laris)) {
        return ['Terlaris', 'lencana-laris'];
    }
    if (in_array($id, $baru)) {
        return ['Baru', 'lencana-baru'];
    }
    return null;
}

function teks_berat($gram)
{
    $gram = (int) $gram;
    if ($gram >= 1000 && $gram % 1000 === 0) {
        return ($gram / 1000) . ' kg';
    }
    return $gram . ' gr';
}

// Satu kolom isian form beserta tempat pesan error.
// $galat berisi pesan per kolom, $atribut berisi atribut tambahan (misalnya validasi).
function field($nama, $label, $tipe, $nilai, $galat, $atribut = '')
{
    $ada = isset($galat[$nama]);
    echo '<div class="mb-3">';
    echo '<label for="f-' . $nama . '" class="form-label">' . e($label) . '</label>';
    echo '<input type="' . $tipe . '" class="form-control' . ($ada ? ' is-invalid' : '') . '" id="f-' . $nama . '" name="' . $nama . '"'
        . ' value="' . e($nilai) . '" data-label="' . e($label) . '" ' . $atribut
        . ' aria-describedby="f-' . $nama . '-pesan"' . ($ada ? ' aria-invalid="true"' : '') . '>';
    echo '<div class="invalid-feedback" id="f-' . $nama . '-pesan">' . e($galat[$nama] ?? '') . '</div>';
    echo '</div>';
}

function field_area($nama, $label, $nilai, $galat, $atribut = '', $baris = 3)
{
    $ada = isset($galat[$nama]);
    echo '<div class="mb-3">';
    echo '<label for="f-' . $nama . '" class="form-label">' . e($label) . '</label>';
    echo '<textarea class="form-control' . ($ada ? ' is-invalid' : '') . '" id="f-' . $nama . '" name="' . $nama . '" rows="' . $baris . '"'
        . ' data-label="' . e($label) . '" ' . $atribut
        . ' aria-describedby="f-' . $nama . '-pesan"' . ($ada ? ' aria-invalid="true"' : '') . '>' . e($nilai) . '</textarea>';
    echo '<div class="invalid-feedback" id="f-' . $nama . '-pesan">' . e($galat[$nama] ?? '') . '</div>';
    echo '</div>';
}

// $pilihan berbentuk [nilai => teks].
function field_pilih($nama, $label, $pilihan, $terpilih, $galat, $atribut = '')
{
    $ada = isset($galat[$nama]);
    echo '<div class="mb-3">';
    echo '<label for="f-' . $nama . '" class="form-label">' . e($label) . '</label>';
    echo '<select class="form-select' . ($ada ? ' is-invalid' : '') . '" id="f-' . $nama . '" name="' . $nama . '"'
        . ' data-label="' . e($label) . '" ' . $atribut
        . ' aria-describedby="f-' . $nama . '-pesan"' . ($ada ? ' aria-invalid="true"' : '') . '>';
    foreach ($pilihan as $nilai => $teks) {
        $pilih = ((string) $nilai === (string) $terpilih) ? ' selected' : '';
        echo '<option value="' . e($nilai) . '"' . $pilih . '>' . e($teks) . '</option>';
    }
    echo '</select>';
    echo '<div class="invalid-feedback" id="f-' . $nama . '-pesan">' . e($galat[$nama] ?? '') . '</div>';
    echo '</div>';
}

// Kartu produk untuk landing, katalog, dan produk terkait.
// Tombol berubah sesuai keadaan: stok habis, sudah login, atau belum login.
function kartu_produk($p)
{
    $id = (int) $p['id_produk'];
    $detail = BASE_URL . 'public/detail.php?id=' . $id;
    $lencana = (int) $p['stok'] > 0 ? lencana_produk($id) : ['Stok Habis', 'lencana-habis'];
    ?>
    <article class="kartu-produk muncul">
        <?php if ($lencana): ?><span class="lencana <?= $lencana[1] ?>"><?= e($lencana[0]) ?></span><?php endif; ?>
        <a class="foto" href="<?= $detail ?>">
            <img src="<?= e(foto_produk($p['gambar'])) ?>" alt="<?= e($p['nama_produk']) ?>" loading="lazy" width="400" height="400">
        </a>
        <div class="isi">
            <p class="kategori-kecil"><?= e($p['nama_kategori']) ?></p>
            <h3 class="nama"><a href="<?= $detail ?>"><?= e($p['nama_produk']) ?></a></h3>
            <p class="berat"><?= e($p['merk']) ?>, <?= e(teks_berat($p['berat_gram'])) ?></p>
            <p class="harga"><?= rupiah($p['harga']) ?></p>
            <?php if ((int) $p['stok'] <= 0): ?>
                <button type="button" class="btn btn-secondary w-100" disabled>Stok Habis</button>
            <?php elseif (sudah_login()): ?>
                <form method="post" action="<?= BASE_URL ?>public/keranjang_aksi.php">
                    <?= csrf_input() ?>
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="id_produk" value="<?= $id ?>">
                    <input type="hidden" name="jumlah" value="1">
                    <input type="hidden" name="kembali" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                    <button type="submit" class="btn btn-biru w-100" aria-label="Tambah <?= e($p['nama_produk']) ?> ke keranjang">+ Keranjang</button>
                </form>
            <?php else: ?>
                <a class="btn btn-biru w-100" href="<?= BASE_URL ?>login-page/login.php?lanjut=<?= urlencode($_SERVER['REQUEST_URI']) ?>" aria-label="Masuk untuk menambah <?= e($p['nama_produk']) ?> ke keranjang">+ Keranjang</a>
            <?php endif; ?>
        </div>
    </article>
    <?php
}
