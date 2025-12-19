<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil ID jadwal
if (!isset($_GET['id'])) {
    header("Location: jadwal.php?error=ID tidak valid");
    exit();
}

$id = $_GET['id'];

// Ambil data jadwal
$stmt = $db->prepare("SELECT * FROM jadwal_pengambilan WHERE id = ?");
$stmt->execute([$id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jadwal) {
    header("Location: jadwal.php?error=Data tidak ditemukan");
    exit();
}

// Ambil produk untuk dropdown
$stmt = $db->prepare("SELECT id, nama_produk, satuan FROM produk ORDER BY nama_produk ASC");
$stmt->execute();
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'];
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $satuan = $_POST['satuan'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];

    $query = "UPDATE jadwal_pengambilan SET 
                produk_id=?, tanggal=?, jumlah=?, satuan=?, deskripsi=?, alamat=?
              WHERE id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$produk_id, $tanggal, $jumlah, $satuan, $deskripsi, $alamat, $id]);

    header("Location: jadwal.php?message=Jadwal berhasil diperbarui");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jadwal Pengambilan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Edit Jadwal Pengambilan</h4>
        </div>
        <div class="card-body">
            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Produk</label>
                    <select name="produk_id" class="form-control" required>
                        <?php foreach ($produk_list as $p): ?>
                            <option value="<?= $p['id']; ?>"
                                <?= $jadwal['produk_id'] == $p['id'] ? 'selected' : '' ?>>
                                <?= $p['nama_produk']; ?> (<?= $p['satuan']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= $jadwal['tanggal']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control"
                           value="<?= $jadwal['jumlah']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" class="form-control"
                           value="<?= $jadwal['satuan']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2"><?= $jadwal['deskripsi']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= $jadwal['alamat']; ?></textarea>
                </div>

                <button class="btn btn-warning" type="submit">Update Jadwal</button>
                <a href="jadwal.php" class="btn btn-secondary">Kembali</a>

            </form>
        </div>
    </div>
</div>

</body>
</html>
