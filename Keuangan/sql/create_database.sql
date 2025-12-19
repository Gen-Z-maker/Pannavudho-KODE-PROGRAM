-- SQL untuk membuat database dan tabel
-- Sistem Manajemen Keuangan Toko Pupuk

-- Buat database
CREATE DATABASE IF NOT EXISTS toko_pupuk_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE toko_pupuk_db;

-- Tabel Users (Pengguna)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori Produk
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(20) UNIQUE NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori_id INT,
    harga_beli DECIMAL(10,2) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    satuan VARCHAR(20) DEFAULT 'kg',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

-- Tabel Supplier
CREATE TABLE IF NOT EXISTS supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(20) UNIQUE NOT NULL,
    tanggal DATE NOT NULL,
    jenis ENUM('pemasukan', 'pengeluaran') NOT NULL,
    kategori_transaksi VARCHAR(50),
    deskripsi TEXT,
    total DECIMAL(10,2) NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT,
    kuantitas DECIMAL(10,2) NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE SET NULL
);

-- Insert data default
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@tokopupuk.com', 'admin'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Satu', 'kasir1@tokopupuk.com', 'kasir');

INSERT INTO kategori (nama_kategori, deskripsi) VALUES 
('Pupuk NPK', 'Pupuk NPK untuk berbagai jenis tanaman'),
('Pupuk Organik', 'Pupuk organik alami'),
('Pupuk Urea', 'Pupuk urea untuk pertumbuhan'),
('Pupuk TSP', 'Pupuk TSP untuk pembungaan'),
('Pupuk KCL', 'Pupuk KCL untuk ketahanan');

INSERT INTO produk (kode_produk, nama_produk, deskripsi, kategori_id, harga_beli, harga_jual, stok, satuan) VALUES 
('NPK001', 'Pupuk NPK 16-16-16', 'Pupuk NPK untuk tanaman padi, jagung, dan sayuran', 1, 45000, 50000, 100, 'kg'),
('ORG001', 'Pupuk Organik Kompos', 'Pupuk organik dari kompos alami', 2, 25000, 35000, 80, 'kg'),
('URE001', 'Pupuk Urea 46%', 'Pupuk urea untuk pertumbuhan tanaman', 3, 40000, 45000, 150, 'kg'),
('TSP001', 'Pupuk TSP 46%', 'Pupuk TSP untuk pembungaan dan pembuahan', 4, 42000, 47000, 75, 'kg'),
('KCL001', 'Pupuk KCL 60%', 'Pupuk KCL untuk ketahanan tanaman', 5, 38000, 43000, 90, 'kg');

INSERT INTO supplier (nama_supplier, alamat, telepon, email) VALUES 
('PT Pupuk Indonesia', 'Jl. Raya Pupuk No. 1, Jakarta', '021-1234567', 'info@pupukindonesia.com'),
('CV Tani Makmur', 'Jl. Pertanian No. 15, Bandung', '022-7654321', 'tani@makmur.com'),
('UD Pupuk Sejahtera', 'Jl. Tani Sejahtera No. 8, Surabaya', '031-9876543', 'sejahtera@pupuk.com');

INSERT INTO transaksi (kode_transaksi, tanggal, jenis, kategori_transaksi, deskripsi, total, user_id) VALUES 
('TRX20240101001', '2024-01-01', 'pemasukan', 'Penjualan', 'Penjualan Pupuk NPK 50kg', 2500000, 1),
('TRX20240102001', '2024-01-02', 'pemasukan', 'Penjualan', 'Penjualan Pupuk Organik 30kg', 1050000, 2),
('TRX20240103001', '2024-01-03', 'pengeluaran', 'Pembelian', 'Pembelian stok pupuk dari supplier', 1800000, 1); 

