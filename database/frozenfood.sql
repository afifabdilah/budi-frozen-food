-- =====================================================================
--  BUDI FROZEN - Pencatatan Produk Frozen Food
--  Skema database + data contoh
--  Import lewat phpMyAdmin, atau lewat terminal:
--    C:\xampp\mysql\bin\mysql.exe -u root < database\frozenfood.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS toko_frozen_food
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE toko_frozen_food;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS supplier;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. kategori : pengelompokan produk
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(80) NOT NULL,
  deskripsi     VARCHAR(255) NULL,
  dibuat        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_nama (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. supplier : pemasok produk
-- ---------------------------------------------------------------------
CREATE TABLE supplier (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_supplier VARCHAR(100) NOT NULL,
  kontak_person VARCHAR(100) NULL,
  no_hp         VARCHAR(20) NULL,
  alamat        VARCHAR(255) NULL,
  dibuat        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_supplier_nama (nama_supplier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3. produk : setiap produk wajib punya 1 kategori dan 1 supplier
--    Kategori/supplier tidak bisa dihapus selama masih dipakai produk.
-- ---------------------------------------------------------------------
CREATE TABLE produk (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_produk  VARCHAR(100) NOT NULL,
  kategori_id  INT UNSIGNED NOT NULL,
  supplier_id  INT UNSIGNED NOT NULL,
  harga        DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok         INT NOT NULL DEFAULT 0,
  satuan       ENUM('pcs','pack','kg','box') NOT NULL DEFAULT 'pcs',
  deskripsi    VARCHAR(255) NULL,
  dibuat       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produk_kategori (kategori_id),
  KEY idx_produk_supplier (supplier_id),
  CONSTRAINT fk_produk_kategori
    FOREIGN KEY (kategori_id) REFERENCES kategori (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT fk_produk_supplier
    FOREIGN KEY (supplier_id) REFERENCES supplier (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
--  DATA CONTOH
-- =====================================================================

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Nugget & Sosis',        'Produk nugget, sosis, dan olahan ayam siap goreng'),
('Bakso & Olahan Daging', 'Bakso sapi, bakso ayam, dan olahan daging beku'),
('Kentang & Snack',       'Kentang goreng dan camilan beku'),
('Seafood Beku',          'Ikan, udang, cumi, dan hasil laut beku'),
('Sayuran Beku',          'Sayuran siap masak yang dibekukan');

INSERT INTO supplier (nama_supplier, kontak_person, no_hp, alamat) VALUES
('PT Frozen Nusantara', 'Andi Wijaya',  '081234500001', 'Jl. Industri Raya No. 12, Tangerang'),
('CV Sumber Beku',      'Rina Melati',  '081234500002', 'Jl. Pasar Baru No. 8, Jakarta'),
('PT Laut Segar',       'Hendra Susanto','081234500003', 'Jl. Pelabuhan No. 45, Muara Baru'),
('UD Dingin Abadi',     'Siti Aminah',  '081234500004', 'Jl. Raya Bogor No. 88, Depok');

INSERT INTO produk (nama_produk, kategori_id, supplier_id, harga, stok, satuan, deskripsi) VALUES
('Nugget Ayam 500 gram',        1, 1, 32000.00, 40, 'pack', 'Nugget ayam premium siap goreng'),
('Sosis Ayam 500 gram',         1, 1, 28000.00, 25, 'pack', 'Sosis ayam tanpa pengawet'),
('Chicken Katsu 500 gram',      1, 2, 38000.00,  9, 'pack', 'Katsu ayam crispy siap goreng'),
('Bakso Sapi 500 gram',         2, 2, 35000.00, 18, 'pack', 'Bakso sapi asli tanpa boraks'),
('Bakso Ayam 500 gram',         2, 2, 27000.00, 35, 'pack', 'Bakso ayam kenyal'),
('Tahu Bakso 300 gram',         2, 4, 24000.00,  0, 'pack', 'Tahu bakso khas Semarang'),
('Kentang Goreng 1 kg',         3, 3, 26000.00, 30, 'kg',   'French fries potongan shoestring'),
('Dimsum Ayam 250 gram',        3, 4, 22500.00, 12, 'pack', 'Dimsum ayam siap kukus'),
('Cireng Isi 250 gram',         3, 4, 15000.00, 50, 'pack', 'Cireng isi ayam pedas'),
('Udang Kupas 500 gram',        4, 3, 65000.00,  6, 'pack', 'Udang vaname kupas beku'),
('Ikan Dori Fillet 500 gram',   4, 3, 45000.00, 15, 'pack', 'Fillet dori tanpa tulang'),
('Mixed Vegetables 500 gram',   5, 1, 20000.00, 22, 'pack', 'Wortel, buncis, jagung, dan kacang polong');
