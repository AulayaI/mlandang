<?php
// Daftar tempat gambar di tampilan website (bukan foto produk).
// Foto produk diunggah lewat Admin > Produk. Gambar di bawah ini DITARUH MANUAL
// ke folder assets/img/tampilan/ dengan nama sesuai daftar ini.
//
// Format boleh jpg, jpeg, png, atau webp (tulis nama tanpa titik-akhiran).
// Contoh: hero-1.jpg, hero-1.png, dan hero-1.webp semuanya dikenali sebagai "hero-1".
// Kalau file belum ada, halaman menampilkan kotak placeholder bertuliskan nama
// file yang harus kamu taruh, jadi tidak ada gambar rusak.

define('DIR_GAMBAR_TAMPILAN', str_replace('\\', '/', (string) realpath(__DIR__ . '/..')) . '/assets/img/tampilan/');
define('URL_GAMBAR_TAMPILAN', BASE_URL . 'assets/img/tampilan/');

// slot => keterangan (dipakai untuk teks alt dan label placeholder).
$GAMBAR_SLOT = [
    'hero-1'    => 'Foto utama beranda (slide 1)',
    'hero-2'    => 'Foto beranda variasi 2 (slide 2, boleh dikosongkan)',
    'hero-3'    => 'Foto beranda variasi 3 (slide 3, boleh dikosongkan)',
    'tentang-1' => 'Foto besar bagian Tentang Kami',
    'tentang-2' => 'Foto kecil Tentang Kami kiri',
    'tentang-3' => 'Foto kecil Tentang Kami kanan',
];

// Gambar kategori di beranda. Nama file = "kategori-" + nama kategori dalam huruf kecil
// tanpa simbol, spasi diganti tanda hubung. Contoh: kategori "Nugget & Ayam Olahan"
// memakai kategori-nugget-ayam-olahan.jpg. Lihat README.txt.
