<?php
require_once 'config/database.php';
session_start();

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$db = (new Database())->getConnection();

// Ambil data produk aktif untuk dropdown
$stmt = $db->prepare("SELECT id, nama_produk FROM produk WHERE status = 'aktif' ORDER BY nama_produk");
$stmt->execute();
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Simpan jadwal pengambilan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "INSERT INTO jadwal_pengambilan (tanggal, produk_id, jumlah, satuan, deskripsi, alamat) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $_POST['tanggal'],
        $_POST['produk_id'],
        $_POST['jumlah'],
        $_POST['satuan'],
        $_POST['deskripsi'],
        $_POST['alamat']
    ]);

    header("Location: jadwal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Jadwal Pengambilan</title>

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
                    <h4>Tambah Jadwal Pengambilan</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label">Produk</label>
                                <select class="form-select" name="produk_id" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($produk_list as $p): ?>
                                        <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['nama_produk']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control" name="jumlah" min="0" placeholder="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Satuan</label>
                                <select class="form-select" name="satuan" required>
                                    <option value="KG">Kilogram (KG)</option>
                                    <option value="Sak">Sak</option>
                                    <option value="Tabung">Tabung</option>
                                    <option value="Pcs">Pcs</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" class="form-control" name="deskripsi" placeholder="Deskripsi (opsional)">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Alamat</label>
                            <input type="text" class="form-control" name="alamat" placeholder="Alamat pengambilan" required>
                        </div>

                        <div class="text-end">
                            <a href="jadwal.php" class="btn btn-secondary">Batal</a>
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