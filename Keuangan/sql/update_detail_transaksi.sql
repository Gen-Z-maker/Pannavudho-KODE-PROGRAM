-- SQL untuk update tabel detail_transaksi yang sudah ada
-- Sistem Manajemen Keuangan Toko Sederhana

-- Pastikan menggunakan database yang benar
USE toko_pupuk_db;

-- 1. Buat tabel detail_transaksi jika belum ada
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    kuantitas DECIMAL(10,2) NOT NULL DEFAULT 0,
    harga_satuan DECIMAL(12,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    
    INDEX idx_transaksi_id (transaksi_id),
    INDEX idx_produk_id (produk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tambahkan kolom user_id ke tabel transaksi jika belum ada
ALTER TABLE transaksi 
ADD COLUMN IF NOT EXISTS user_id INT;

-- 3. Set user_id untuk transaksi yang sudah ada (gunakan admin sebagai default)
UPDATE transaksi 
SET user_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1)
WHERE user_id IS NULL;

-- 4. Tambahkan foreign key untuk user_id
ALTER TABLE transaksi 
ADD FOREIGN KEY IF NOT EXISTS (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- 5. Tambahkan index untuk performa
ALTER TABLE transaksi 
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_tanggal (tanggal),
ADD INDEX IF NOT EXISTS idx_jenis (jenis);

-- 6. Insert contoh data detail transaksi untuk transaksi yang sudah ada
-- Hanya untuk transaksi yang belum memiliki detail
INSERT INTO detail_transaksi (transaksi_id, produk_id, kuantitas, harga_satuan, subtotal)
SELECT 
    t.id as transaksi_id,
    p.id as produk_id,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN FLOOR(RAND() * 5) + 1  -- 1-5 untuk penjualan
        ELSE FLOOR(RAND() * 3) + 1  -- 1-3 untuk pembelian
    END as kuantitas,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN p.harga_jual
        ELSE p.harga_beli
    END as harga_satuan,
    CASE 
        WHEN t.jenis = 'pemasukan' THEN p.harga_jual * (FLOOR(RAND() * 5) + 1)
        ELSE p.harga_beli * (FLOOR(RAND() * 3) + 1)
    END as subtotal
FROM transaksi t
CROSS JOIN produk p
WHERE t.id NOT IN (SELECT DISTINCT transaksi_id FROM detail_transaksi)
AND p.status = 'aktif'
AND t.total > 0
LIMIT 20;  -- Batasi agar tidak terlalu banyak data

-- 7. Update total transaksi berdasarkan detail_transaksi
UPDATE transaksi t
SET total = (
    SELECT COALESCE(SUM(subtotal), 0)
    FROM detail_transaksi dt
    WHERE dt.transaksi_id = t.id
)
WHERE EXISTS (
    SELECT 1 FROM detail_transaksi dt WHERE dt.transaksi_id = t.id
);

-- 8. Tampilkan hasil
SELECT 
    'detail_transaksi' as table_name,
    COUNT(*) as total_records
FROM detail_transaksi
UNION ALL
SELECT 
    'transaksi' as table_name,
    COUNT(*) as total_records
FROM transaksi;

-- 9. Tampilkan contoh data detail transaksi
SELECT 
    dt.id,
    dt.transaksi_id,
    t.kode_transaksi,
    p.nama_produk,
    dt.kuantitas,
    dt.harga_satuan,
    dt.subtotal
FROM detail_transaksi dt
JOIN transaksi t ON dt.transaksi_id = t.id
JOIN produk p ON dt.produk_id = p.id
ORDER BY dt.transaksi_id, dt.id
LIMIT 10;


