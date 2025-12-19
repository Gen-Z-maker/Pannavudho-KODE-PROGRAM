-- SQL untuk membuat tabel detail_transaksi
-- Sistem Manajemen Keuangan Toko Sederhana

-- Pastikan menggunakan database yang benar
USE toko_pupuk_db;

-- Buat tabel detail_transaksi jika belum ada
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    kuantitas DECIMAL(10,2) NOT NULL DEFAULT 0,
    harga_satuan DECIMAL(12,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    
    -- Index untuk performa
    INDEX idx_transaksi_id (transaksi_id),
    INDEX idx_produk_id (produk_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambahkan kolom user_id ke tabel transaksi jika belum ada
ALTER TABLE transaksi 
ADD COLUMN IF NOT EXISTS user_id INT,
ADD FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Tambahkan index untuk performa
ALTER TABLE transaksi 
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_tanggal (tanggal),
ADD INDEX IF NOT EXISTS idx_jenis (jenis),
ADD INDEX IF NOT EXISTS idx_kode_transaksi (kode_transaksi);

-- Insert contoh data detail transaksi (jika ada transaksi yang sudah ada)
-- Hapus komentar di bawah ini jika ingin menambahkan data contoh

/*
-- Contoh data detail transaksi untuk transaksi yang sudah ada
INSERT INTO detail_transaksi (transaksi_id, produk_id, kuantitas, harga_satuan, subtotal)
SELECT 
    t.id as transaksi_id,
    p.id as produk_id,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN FLOOR(RAND() * 10) + 1  -- 1-10 untuk penjualan
        ELSE FLOOR(RAND() * 5) + 1  -- 1-5 untuk pembelian
    END as kuantitas,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN p.harga_jual
        ELSE p.harga_beli
    END as harga_satuan,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN p.harga_jual * (FLOOR(RAND() * 10) + 1)
        ELSE p.harga_beli * (FLOOR(RAND() * 5) + 1)
    END as subtotal
FROM transaksi t
CROSS JOIN produk p
WHERE t.id IN (SELECT id FROM transaksi LIMIT 3)  -- Hanya untuk 3 transaksi pertama
AND p.status = 'aktif'
LIMIT 10;
*/

-- Update total transaksi berdasarkan detail_transaksi
UPDATE transaksi t
SET total = (
    SELECT COALESCE(SUM(subtotal), 0)
    FROM detail_transaksi dt
    WHERE dt.transaksi_id = t.id
)
WHERE EXISTS (
    SELECT 1 FROM detail_transaksi dt WHERE dt.transaksi_id = t.id
);

-- Tampilkan informasi tabel yang telah dibuat
SELECT 
    'detail_transaksi' as table_name,
    COUNT(*) as total_records
FROM detail_transaksi
UNION ALL
SELECT 
    'transaksi' as table_name,
    COUNT(*) as total_records
FROM transaksi;

-- Tampilkan struktur tabel detail_transaksi
DESCRIBE detail_transaksi;


