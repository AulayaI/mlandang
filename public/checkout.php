<?php
// Checkout: isi data pengambil, pilih metode bayar, lalu pesanan dibuat.
// Pembeli mengisi nama, nomor WhatsApp, dan pesan. Email diambil otomatis dari akun
// pembeli, jadi admin tetap punya data email tanpa pembeli mengetiknya dua kali.
require_once __DIR__ . '/../includes/awal.php';
wajib_login();

$item = isi_keranjang();
if (!$item) {
    set_pesan('gagal', 'Keranjangmu masih kosong.');
    arahkan(BASE_URL . 'public/keranjang.php');
}

// Total dihitung ulang dari harga di database. Angka dari form tidak pernah dipercaya.
$total = 0;
foreach ($item as $b) {
    if ($b['status'] !== 'aktif' || (int) $b['stok'] < (int) $b['jumlah']) {
        set_pesan('gagal', 'Ada produk di keranjang yang stoknya tidak cukup. Perbaiki dulu sebelum checkout.');
        arahkan(BASE_URL . 'public/keranjang.php');
    }
    $total += $b['harga'] * $b['jumlah'];
}

$akun = db_satu('SELECT nama, email, no_wa FROM pembeli WHERE id_pembeli = ?', 'i', [id_pengguna()]);
$nilai = [
    'nama_penerima'  => $akun['nama'],
    'no_wa_penerima' => $akun['no_wa'],
    'catatan'        => '',
    'metode_bayar'   => '',
];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    foreach (array_keys($nilai) as $kolom) {
        $nilai[$kolom] = trim($_POST[$kolom] ?? '');
    }

    if (mb_strlen($nilai['nama_penerima']) < 3 || mb_strlen($nilai['nama_penerima']) > 100) {
        $galat['nama_penerima'] = 'Nama pengambil 3 sampai 100 karakter.';
    }
    if (!nomor_wa_valid($nilai['no_wa_penerima'])) {
        $galat['no_wa_penerima'] = 'Nomor WhatsApp berupa angka, contoh: 081234567890.';
    }
    if (mb_strlen($nilai['catatan']) > 500) {
        $galat['catatan'] = 'Pesan maksimal 500 karakter.';
    }
    if (!in_array($nilai['metode_bayar'], ['qris', 'ewallet'], true)) {
        $galat['metode_bayar'] = 'Pilih salah satu metode pembayaran.';
    }

    if (!$galat) {
        db_mulai();
        $nama_dicek = '';
        try {
            // Satu baris membeli untuk tiap produk. Semua baris satu checkout memakai kode_pesanan yang sama.
            $kode = buat_kode_pesanan();
            $sekarang = date('Y-m-d H:i:s');
            foreach ($item as $b) {
                $nama_dicek = $b['nama_produk'];

                // 1. Catat pesanan. Trigger trg_cek_stok_membeli di database menolak baris ini kalau stok kurang.
                db_jalankan(
                    "INSERT INTO membeli (kode_pesanan, id_pembeli, id_produk, jumlah, harga_satuan, tgl_pesanan, metode_bayar, status,
                                          nama_penerima, email_penerima, no_wa_penerima, catatan)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu_bayar', ?, ?, ?, ?)",
                    'siiidssssss',
                    [
                        $kode, id_pengguna(), $b['id_produk'], $b['jumlah'], $b['harga'], $sekarang, $nilai['metode_bayar'],
                        $nilai['nama_penerima'], $akun['email'], $nilai['no_wa_penerima'],
                        $nilai['catatan'] !== '' ? $nilai['catatan'] : null,
                    ]
                );

                // 2. Kurangi stok dengan satu perintah bersyarat (stok >= jumlah).
                // Kalau dua pembeli memesan barang terakhir bersamaan, hanya satu yang berhasil,
                // jadi stok tidak pernah minus. Kalau gagal, seluruh pesanan dibatalkan (rollback).
                $berhasil = db_jalankan(
                    "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND status = 'aktif' AND stok >= ?",
                    'iii',
                    [$b['jumlah'], $b['id_produk'], $b['jumlah']]
                );
                if ($berhasil !== 1) {
                    throw new RuntimeException('Stok ' . $b['nama_produk'] . ' baru saja berkurang dan tidak cukup lagi. Periksa keranjangmu.');
                }
            }
            db_simpan();
            keranjang_simpan([]);
        } catch (mysqli_sql_exception $e) {
            // Harus di atas catch RuntimeException, karena mysqli_sql_exception turunan RuntimeException.
            // Kode 1644 = pesan SIGNAL dari trigger stok. Error SQL lain tidak boleh tampil ke pembeli.
            db_batal();
            if ($e->getCode() !== 1644) {
                throw $e;
            }
            $galat['umum'] = 'Stok ' . $nama_dicek . ' tidak cukup lagi. Periksa keranjangmu.';
        } catch (RuntimeException $e) {
            db_batal();
            $galat['umum'] = $e->getMessage();
        } catch (Throwable $e) {
            db_batal();
            throw $e;
        }

        if (!$galat) {
            arahkan(BASE_URL . 'public/pembayaran.php?kode=' . urlencode($kode));
        }
    }
}

$judul = 'Checkout';
$menu_aktif = '';
require __DIR__ . '/../includes/header.php';
?>
<div class="kepala-halaman">
    <div class="container"><h1>Checkout</h1></div>
</div>

<div class="container seksi-kecil">
    <?php tampil_pesan(); ?>
    <?php if (isset($galat['umum'])): ?>
        <div class="alert alert-danger" role="alert"><?= e($galat['umum']) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="" data-validasi>
                <?= csrf_input() ?>
                <h2 class="h5 mb-3">Data pengambil</h2>
                <?php field('nama_penerima', 'Nama pengambil', 'text', $nilai['nama_penerima'], $galat, 'data-wajib data-min="3" maxlength="100"'); ?>
                <?php field('no_wa_penerima', 'Nomor WhatsApp', 'tel', $nilai['no_wa_penerima'], $galat, 'data-wajib data-tipe="telepon" maxlength="20"'); ?>
                <?php field_area('catatan', 'Pesan untuk toko (boleh kosong)', $nilai['catatan'], $galat, 'maxlength="500"', 2); ?>

                <fieldset class="mb-4" data-radio-wajib>
                    <legend class="form-label fs-6">Metode pembayaran</legend>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metode_bayar" id="m-qris" value="qris"<?= $nilai['metode_bayar'] === 'qris' ? ' checked' : '' ?>>
                        <label class="form-check-label" for="m-qris">QRIS</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metode_bayar" id="m-ewallet" value="ewallet"<?= $nilai['metode_bayar'] === 'ewallet' ? ' checked' : '' ?>>
                        <label class="form-check-label" for="m-ewallet">Transfer <?= e(EWALLET_NAMA) ?></label>
                    </div>
                    <div class="invalid-feedback"<?= isset($galat['metode_bayar']) ? ' style="display:block"' : '' ?>><?= e($galat['metode_bayar'] ?? '') ?></div>
                </fieldset>

                <button type="submit" class="btn btn-aksen btn-lg">Buat Pesanan dan Lanjut Bayar</button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="ringkasan">
                <h2 class="h5">Pesananmu</h2>
                <div class="info-ambil"><i class="bi bi-bag-check" aria-hidden="true"></i><div><strong>Diambil sendiri di toko.</strong><br><?= e(ALAMAT_TOKO) ?></div></div>
                <ul>
                    <?php foreach ($item as $b): ?>
                        <li><span><?= e($b['nama_produk']) ?> x <?= (int) $b['jumlah'] ?></span><span><?= rupiah($b['harga'] * $b['jumlah']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="total"><span>Total</span><span class="angka"><?= rupiah($total) ?></span></div>
                <a class="btn btn-garis w-100 mt-3" href="<?= BASE_URL ?>public/keranjang.php">Ubah Isi Keranjang</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
