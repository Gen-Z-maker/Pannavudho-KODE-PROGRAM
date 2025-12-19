<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil data kategori
$stmt = $db->prepare("SELECT * FROM kategori ORDER BY nama_kategori");
$stmt->execute();
$kategori_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Simpan produk baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "INSERT INTO produk (kode_produk, nama_produk, deskripsi, kategori_id, harga_beli, harga_jual, stok, satuan) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $_POST['kode_produk'],
        $_POST['nama_produk'],
        $_POST['deskripsi'],
        $_POST['kategori_id'],
        $_POST['harga_beli'],
        $_POST['harga_jual'],
        $_POST['stok'],
        $_POST['satuan']
    ]);

    header("Location: produkkaryawan.php"); // kembali ke halaman produk
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Produk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #f5f6fa; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #198754, #28a745);
            color: #fff;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header text-center py-3">
                    <h4>Tambah Produk</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Produk</label>
                                <input type="text" class="form-control" name="kode_produk" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" name="nama_produk" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="kategori_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_list as $k): ?>
                                    <option value="<?= $k['id']; ?>"><?= $k['nama_kategori']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga Beli (Rp)</label>
                                <input type="number" class="form-control" name="harga_beli" min="0" step="1000" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga Jual (Rp)</label>
                                <input type="number" class="form-control" name="harga_jual" min="0" step="1000" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stok</label>
                                <input type="number" class="form-control" name="stok" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Satuan</label>
                            <select class="form-select" name="satuan" required>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="gram">Gram (g)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="sak">Sak</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <a href="produkkaryawan.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-success px-4">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>