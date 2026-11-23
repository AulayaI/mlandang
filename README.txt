MLANDANG FROZEN FOOD  |  folder X1-FF-PA_Ly
Panduan singkat: file mana yang diedit untuk mengganti isi website.
=====================================================================

1. CARA MENJALANKAN (LARAGON)
   a. Taruh folder X1-FF-PA_Ly di C:\laragon\www\
   b. Buka phpMyAdmin, buat database bernama frozen_food_web
   c. Import file database/frozen_food_web.sql
   d. Buka http://localhost/X1-FF-PA_Ly/
   Kalau user atau password MySQL berbeda, ubah di config/koneksi.php

   Akun contoh (dari file SQL):
   - Admin     : admin@mlandang.test  /  Admin123!
   - Pelanggan : budi@contoh.test     /  Pelanggan123!

2. MENGGANTI NOMOR WHATSAPP
   File    : config/toko.php  ->  NOMOR_WA_TOKO
   Format  : 6281259204220 (kode negara 62, tanpa + dan tanpa 0 di depan)
   Satu nomor ini dipakai oleh tombol WhatsApp hijau melayang, tombol di
   footer, bagian Lokasi, dan konfirmasi pembayaran.

3. MENGGANTI DATA TOKO
   File    : config/toko.php
   - NAMA_TOKO, TAGLINE            nama dan kalimat singkat toko
   - TENTANG_TOKO                  teks bagian "Tentang Kami"
   - ALAMAT_TOKO                   alamat (beranda, footer, halaman pembayaran)
   - KATA_KUNCI_PETA               kata kunci pencarian di Google Maps
   - GAMBAR_QRIS                   nama file gambar QRIS
   - EWALLET_NAMA, EWALLET_NOMOR, EWALLET_ATAS_NAMA   tujuan transfer
   - MODE_DEMO                     true = tampil peringatan demo, ubah ke false
                                   kalau QRIS dan nomor sudah asli

4. MENGGANTI GAMBAR TAMPILAN (hero, Tentang Kami, kategori)
   Folder  : assets/img/tampilan/
   Daftar slot ada di config/gambar.php. Format boleh jpg, jpeg, png, atau webp.
   Selama file belum ada, halaman menampilkan kotak putus-putus bertuliskan
   nama file yang harus kamu taruh, jadi tidak ada gambar rusak.

   Nama file                      Tampil di mana
   -----------------------------  ------------------------------------------
   hero-1.jpg                     Slide 1 di bagian paling atas beranda
   hero-2.jpg, hero-3.jpg         Slide 2 dan 3 (boleh tidak dibuat)
   tentang-1.jpg                  Foto besar di bagian Tentang Kami
   tentang-2.jpg, tentang-3.jpg   Dua foto kecil di Tentang Kami
   kategori-(nama).jpg            Gambar kartu kategori di beranda

   Aturan nama gambar kategori: tulis "kategori-" lalu nama kategori dengan
   huruf kecil, simbol dibuang, spasi jadi tanda hubung. Contoh:
     "Nugget & Ayam Olahan"   ->  kategori-nugget-ayam-olahan.jpg
     "Sosis & Daging Olahan"  ->  kategori-sosis-daging-olahan.jpg
     "Bakso & Olahan Bulat"   ->  kategori-bakso-olahan-bulat.jpg
   (Nama persis yang dibutuhkan tertulis di kotak placeholder pada beranda.)

5. MENGGANTI FOTO PRODUK
   Tidak lewat folder. Masuk sebagai admin -> menu Produk -> Tambah atau Ubah
   -> pilih file gambar (JPG/PNG/WEBP, maksimal 2 MB). File tersimpan otomatis
   di assets/img/produk/.
   Produk yang dicentang "Tampilkan di slider" muncul sebagai slide berikutnya
   setelah gambar hero di beranda.

6. MENGGANTI WARNA DAN TAMPILAN
   File    : assets/css/style.css
   Warna utama ada di bagian :root paling atas (--navy, --biru, --aksen, dst).

7. MENGGANTI IKON
   Ikon memakai library Bootstrap Icons (dimuat dari internet lewat CDN di
   includes/head_umum.php). Cara pakai di HTML:  <i class="bi bi-cart3"></i>
   Cari nama ikon di https://icons.getbootstrap.com
   Di PHP ada pembungkus kecil:  ikon('cart3')  (includes/komponen.php).
   Catatan: tanpa internet, ikon tidak tampil. Font Plus Jakarta Sans juga
   dari internet, kalau offline dipakai font bawaan perangkat.

8. PETA FILE
   config/        toko.php (data toko), gambar.php (slot gambar), koneksi.php (database)
   includes/      header.php, footer.php   kerangka halaman pembeli (logo, ikon, tab, footer, tombol WA)
                  admin_header.php         sidebar dan kerangka admin
                  auth_atas.php/auth_bawah.php  kerangka login dan daftar
                  komponen.php             kartu produk, ikon, placeholder gambar, kolom form
                  db.php                   fungsi database (prepared statement)
                  keranjang.php, pesanan.php  aturan keranjang dan pesanan
   public/        index.php (beranda), produk.php, detail.php, keranjang.php,
                  checkout.php, pembayaran.php, pesanan_saya.php
   login-page/    login.php, register.php, logout.php
   admin/         index.php (dashboard), pesanan, produk, transaksi (stok masuk),
                  supplier, pengguna
   assets/        css/style.css, js/main.js (slider, validasi form), lib/, img/
   database/      frozen_food_web.sql
   dokumentasi/   ERD_chen_v0.5.png, ERD_tabel_v0.5.png, DFD_level0.png, DFD_level1.png

9. ALUR PEMESANAN (untuk flowchart)
   Pembeli pilih produk -> masuk keranjang -> checkout (pilih QRIS atau transfer
   e-wallet) -> halaman pembayaran -> tombol konfirmasi membuka WhatsApp dengan
   rincian pesanan terisi -> admin cek dan ubah status -> pesanan disiapkan ->
   pembeli ambil sendiri di toko. Tidak ada alamat pengiriman.
   Status pesanan: menunggu_bayar -> menunggu_konfirmasi -> diproses -> selesai
   (atau batal kapan saja sebelum selesai, stok dikembalikan).

10. SAAT DIUNGGAH KE HOSTING
   - Ubah MODE_DEBUG di config/app.php menjadi false
   - Isi data database hosting di config/koneksi.php
   - Folder assets/img/produk harus bisa ditulis (izin 755 atau 775)
