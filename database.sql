-- ============================================
-- DATABASE KASIR
-- ============================================

CREATE DATABASE IF NOT EXISTS kasir_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE kasir_db;

-- ============================================
-- TABLE ADMIN
-- ============================================

CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Default login:
-- Username : admin
-- Password : admin123

INSERT INTO admin (username, password)
SELECT
    'admin',
    '$2y$12$5fQOYw.06w3Gu48UCCK1AOKi21y5bDnbng8Lr6qU8jLVLD/QwZuei'
WHERE NOT EXISTS (
    SELECT 1
    FROM admin
    WHERE username = 'admin'
);

-- ============================================
-- TABLE PELANGGAN
-- ============================================

CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(20)
);

-- ============================================
-- TABLE BARANG
-- ============================================

CREATE TABLE IF NOT EXISTS barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0
);

-- ============================================
-- TABLE PENJUALAN
-- ============================================

CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,

    id_pelanggan INT NOT NULL,
    id_barang INT NOT NULL,

    jumlah INT NOT NULL DEFAULT 1,
    harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,

    waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_penjualan_pelanggan
        FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_penjualan_barang
        FOREIGN KEY (id_barang)
        REFERENCES barang(id_barang)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ============================================
-- SAMPLE PELANGGAN
-- ============================================

INSERT INTO pelanggan (
    nama_pelanggan,
    alamat,
    no_hp
)
SELECT
    'Budi Santoso',
    'Surabaya',
    '081234567890'
WHERE NOT EXISTS (
    SELECT 1
    FROM pelanggan
    WHERE no_hp = '081234567890'
);

-- ============================================
-- SAMPLE BARANG
-- ============================================

INSERT INTO barang (
    nama_barang,
    harga,
    stok
)
SELECT
    'Air Mineral',
    5000,
    50
WHERE NOT EXISTS (
    SELECT 1
    FROM barang
    WHERE nama_barang = 'Air Mineral'
);

INSERT INTO barang (
    nama_barang,
    harga,
    stok
)
SELECT
    'Mie Instan',
    3500,
    100
WHERE NOT EXISTS (
    SELECT 1
    FROM barang
    WHERE nama_barang = 'Mie Instan'
);

INSERT INTO barang (
    nama_barang,
    harga,
    stok
)
SELECT
    'Teh Botol',
    5000,
    50
WHERE NOT EXISTS (
    SELECT 1
    FROM barang
    WHERE nama_barang = 'Teh Botol'
);