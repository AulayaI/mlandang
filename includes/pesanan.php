<?php
// Aturan pesanan yang dipakai pembeli dan admin.
//
// Satu pesanan = beberapa baris di tabel membeli yang memakai kode_pesanan yang sama
// (satu baris untuk tiap produk). Data pesanan seperti status dan data pengambil disimpan
// sama di tiap barisnya, jadi perubahan status selalu mengubah semua baris sekaligus.
//
// Stok berkurang saat checkout (barang "dipegang" untuk pembeli) dan
// dikembalikan kalau pesanan dibatalkan. Dengan begitu stok di halaman toko
// selalu menunjukkan barang yang benar-benar masih bisa dipesan.

// Kode pesanan: ORD-tanggal-urutan, contoh ORD-261118-0003.
// Angka terakhir adalah urutan pesanan di database (pesanan ke-3, ke-19, ke-135, dst).
function buat_kode_pesanan()
{
    $hasil = db_satu('SELECT COUNT(DISTINCT kode_pesanan) AS jumlah FROM membeli');
    $urutan = (int) $hasil['jumlah'];
    do {
        $urutan++;
        $kode = 'ORD-' . date('ymd') . '-' . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    } while (db_satu('SELECT 1 AS ada FROM membeli WHERE kode_pesanan = ?', 's', [$kode]));
    return $kode;
}

// Batas bayar sebuah pesanan (detik sejak 1970), dihitung dari waktu pesan.
function batas_bayar($tgl_pesanan)
{
    return strtotime($tgl_pesanan) + BATAS_BAYAR_MENIT * 60;
}

// Membatalkan otomatis semua pesanan yang belum dikonfirmasi dan sudah lewat batas bayar.
// Dipanggil di awal setiap halaman (includes/awal.php), jadi tidak perlu cron.
// Statusnya 'kedaluwarsa' (beda dari 'batal' yang dilakukan orang) dan stok dikembalikan.
function kedaluwarsakan_pesanan_lama()
{
    $batas = date('Y-m-d H:i:s', time() - BATAS_BAYAR_MENIT * 60);
    $lama = db_semua(
        "SELECT DISTINCT kode_pesanan FROM membeli WHERE status = 'menunggu_bayar' AND tgl_pesanan <= ?",
        's',
        [$batas]
    );
    foreach ($lama as $p) {
        batalkan_pesanan($p['kode_pesanan'], 'kedaluwarsa');
    }
}

// Mengambil satu pesanan: data umum ($hasil['pesanan']) dan daftar barangnya ($hasil['item']).
// Mengembalikan null kalau kode tidak ada.
function ambil_pesanan($kode)
{
    $item = db_semua(
        'SELECT m.id_membeli, m.id_pembeli, m.id_produk, m.jumlah AS jumlah_beli, m.harga_satuan,
                m.jumlah * m.harga_satuan AS subtotal, m.tgl_pesanan, m.metode_bayar, m.status,
                m.nama_penerima, m.email_penerima, m.no_wa_penerima, m.catatan,
                p.nama_produk
         FROM membeli m JOIN produk p ON p.id_produk = m.id_produk
         WHERE m.kode_pesanan = ?
         ORDER BY m.id_membeli',
        's',
        [$kode]
    );
    if (!$item) {
        return null;
    }
    $pesanan = $item[0];
    $pesanan['kode_pesanan'] = $kode;
    $pesanan['total_bayar'] = array_sum(array_column($item, 'subtotal'));
    return ['pesanan' => $pesanan, 'item' => $item];
}

// Pilihan status yang boleh dipilih admin dari status sekarang.
// 'selesai' dan 'batal' adalah akhir: tidak bisa diubah lagi.
function status_berikutnya($status)
{
    $alur = [
        'menunggu_bayar'      => ['diproses', 'batal'],
        'menunggu_konfirmasi' => ['diproses', 'batal'],
        'diproses'            => ['selesai', 'batal'],
        'selesai'             => [],
        'batal'               => [],
        'kedaluwarsa'         => [],
    ];
    return $alur[$status] ?? [];
}

// Mengembalikan true kalau pesanan berhasil dibatalkan.
// $status_akhir: 'batal' (dibatalkan pembeli atau admin) atau 'kedaluwarsa' (batas bayar habis).
// Pesanan kedaluwarsa hanya yang masih menunggu_bayar, supaya pesanan yang sudah
// dikonfirmasi pembeli tidak ikut dibatalkan sistem.
function batalkan_pesanan($kode, $status_akhir = 'batal')
{
    $syarat = $status_akhir === 'kedaluwarsa'
        ? "status = 'menunggu_bayar'"
        : "status NOT IN ('batal', 'kedaluwarsa', 'selesai')";
    db_mulai();
    try {
        // Status diubah lebih dulu dengan syarat. Kalau dua permintaan datang
        // bersamaan, hanya satu yang lolos, jadi stok tidak dikembalikan dua kali.
        $diubah = db_jalankan(
            "UPDATE membeli SET status = ? WHERE kode_pesanan = ? AND $syarat",
            'ss',
            [$status_akhir, $kode]
        );
        if ($diubah < 1) {
            db_batal();
            return false;
        }
        $baris = db_semua('SELECT id_produk, jumlah FROM membeli WHERE kode_pesanan = ?', 's', [$kode]);
        foreach ($baris as $b) {
            db_jalankan('UPDATE produk SET stok = stok + ? WHERE id_produk = ?', 'ii', [$b['jumlah'], $b['id_produk']]);
        }
        db_simpan();
        return true;
    } catch (Throwable $e) {
        db_batal();
        throw $e;
    }
}

// Mengembalikan teks error, atau '' kalau berhasil.
function ubah_status_pesanan($kode, $status_baru)
{
    $p = db_satu('SELECT status FROM membeli WHERE kode_pesanan = ?', 's', [$kode]);
    if (!$p) {
        return 'Pesanan tidak ditemukan.';
    }
    if (!in_array($status_baru, status_berikutnya($p['status']), true)) {
        return 'Perubahan status itu tidak diizinkan dari status sekarang.';
    }
    if ($status_baru === 'batal') {
        return batalkan_pesanan($kode) ? '' : 'Pesanan sudah tidak bisa dibatalkan.';
    }
    db_jalankan('UPDATE membeli SET status = ? WHERE kode_pesanan = ?', 'ss', [$status_baru, $kode]);
    return '';
}

// Teks konfirmasi yang dikirim pembeli ke WhatsApp toko.
// Total diambil dari database, bukan dari form, jadi tidak bisa diubah pembeli.
function teks_konfirmasi_wa($pesanan, $item)
{
    $baris = [];
    $baris[] = 'Halo ' . NAMA_TOKO . ', saya ingin konfirmasi pembayaran.';
    $baris[] = '';
    $baris[] = 'No. pesanan: ' . $pesanan['kode_pesanan'];
    $baris[] = 'Nama: ' . $pesanan['nama_penerima'];
    $baris[] = 'Metode bayar: ' . nama_metode($pesanan['metode_bayar']);
    $baris[] = 'Pesanan diambil sendiri di toko.';
    $baris[] = '';
    $baris[] = 'Pesanan:';
    foreach ($item as $i) {
        $baris[] = '- ' . $i['nama_produk'] . ' x ' . $i['jumlah_beli'] . ' = ' . rupiah($i['subtotal']);
    }
    $baris[] = '';
    $baris[] = 'Total: ' . rupiah($pesanan['total_bayar']);
    $baris[] = '';
    $baris[] = 'Foto bukti pembayaran saya kirim di chat ini.';
    return implode("\n", $baris);
}
