-- Mlandang Frozen Food: skema 5 tabel dan data contoh.
-- Entitas: supplier, produk, pembeli. Relasi: transaksi (supplier-produk) dan membeli (pembeli-produk).
-- Data awal hanya 1-2 baris per tabel (data contoh). Selebihnya ditambah lewat dashboard admin.
-- Impor lewat phpMyAdmin setelah membuat database (default: frozen_food_web).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS membeli;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pembeli;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE supplier (
  id_supplier INT AUTO_INCREMENT PRIMARY KEY,
  nama_supplier VARCHAR(100) NOT NULL,
  no_telp VARCHAR(20) NOT NULL,
  alamat VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk (
  id_produk INT AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(120) NOT NULL,
  kategori VARCHAR(60) NOT NULL,
  merk VARCHAR(60) NOT NULL,
  berat_gram INT NOT NULL,
  harga DECIMAL(10,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  tgl_kadaluarsa DATE NULL,
  deskripsi TEXT NULL,
  gambar VARCHAR(120) NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pembeli (
  id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  no_wa VARCHAR(20) NOT NULL,
  role ENUM('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relasi supplier - produk: stok masuk. Satu baris = satu produk dari satu supplier.
CREATE TABLE transaksi (
  id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
  id_supplier INT NOT NULL,
  id_produk INT NOT NULL,
  jumlah INT NOT NULL,
  harga_beli DECIMAL(10,2) NOT NULL,
  tgl_transaksi DATETIME NOT NULL,
  FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier),
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relasi pembeli - produk: pesanan. Satu baris = satu produk dalam satu pesanan.
-- Baris dengan kode_pesanan yang sama adalah satu pesanan (satu kali checkout).
CREATE TABLE membeli (
  id_membeli INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan VARCHAR(20) NOT NULL,
  id_pembeli INT NOT NULL,
  id_produk INT NOT NULL,
  jumlah INT NOT NULL,
  harga_satuan DECIMAL(10,2) NOT NULL,
  tgl_pesanan DATETIME NOT NULL,
  metode_bayar ENUM('qris','ewallet') NOT NULL,
  status ENUM('menunggu_bayar','menunggu_konfirmasi','diproses','selesai','batal','kedaluwarsa') NOT NULL DEFAULT 'menunggu_bayar',
  nama_penerima VARCHAR(100) NOT NULL,
  email_penerima VARCHAR(100) NOT NULL,
  no_wa_penerima VARCHAR(20) NOT NULL,
  catatan VARCHAR(500) NULL,
  KEY idx_kode (kode_pesanan),
  FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli),
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun contoh. Admin: admin@mlandang.test / Admin123!  Pelanggan: budi@contoh.test / Pelanggan123!
INSERT INTO pembeli (nama,email,password,no_wa,role,created_at) VALUES
('Admin Mlandang','admin@mlandang.test','$2y$10$36sq/newexAHvgZUWj5OWeVe8QQDMWIlhJwE/n1nSFq8l8ffvkiSS','081200000000','admin',NOW()),
('Budi Santoso','budi@contoh.test','$2y$10$EXcfz4aEfhbHecP9XBCSLe91I0jE99Bg.UsKIBwYsb.WiYGpeoOZC','081234500001','pelanggan',NOW());

INSERT INTO produk (nama_produk,kategori,merk,berat_gram,harga,stok,tgl_kadaluarsa,deskripsi,gambar,status) VALUES
('Fiesta Chicken Nugget Stikie','Nugget & Ayam Olahan','Fiesta',500,48000,38,DATE_ADD(CURDATE(), INTERVAL 240 DAY),'Contoh produk. Simpan di freezer (-18 C) dan masak sampai matang.','fiesta-nugget.svg','aktif'),
('Nidia Sosis Sapi Bockwurst','Sosis & Daging Olahan','Nidia',500,42000,30,DATE_ADD(CURDATE(), INTERVAL 200 DAY),'Contoh produk. Simpan di freezer (-18 C) dan masak sampai matang.','nidia-bockwurst.svg','aktif');

INSERT INTO supplier (nama_supplier,no_telp,alamat) VALUES ('CV Beku Jaya Sentosa','081211110001','Jl. Contoh No. 10, Kediri');

INSERT INTO transaksi (id_supplier,id_produk,jumlah,harga_beli,tgl_transaksi) VALUES
(1,1,40,38000,DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1,2,30,33000,DATE_SUB(NOW(), INTERVAL 5 DAY));

INSERT INTO membeli (kode_pesanan,id_pembeli,id_produk,jumlah,harga_satuan,tgl_pesanan,metode_bayar,status,nama_penerima,email_penerima,no_wa_penerima,catatan) VALUES
('ORD-CONTOH-0001',2,1,2,48000,DATE_SUB(NOW(), INTERVAL 2 DAY),'qris','selesai','Budi Santoso','budi@contoh.test','081234500001',NULL);
