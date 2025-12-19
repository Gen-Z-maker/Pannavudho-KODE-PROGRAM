<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$transaksi_id) {
    header("Location: dashboard.php");
    exit();
}

// Ambil data transaksi
$query = "SELECT t.*, u.nama_lengkap as nama_user 
          FROM transaksi t 
          LEFT JOIN users u ON t.user_id = u.id 
          WHERE t.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$transaksi_id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    header("Location: dashboard.php");
    exit();
}

// Ambil detail transaksi
$query = "SELECT dt.*, p.nama_produk, p.kode_produk, p.satuan 
          FROM detail_transaksi dt 
          LEFT JOIN produk p ON dt.produk_id = p.id 
          WHERE dt.transaksi_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$transaksi_id]);
$detail_transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Toko Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; width: 200px; min-width: 200px; max-width: 220px; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1); background-color: #f8f9fa; }
        .sidebar .nav-link { font-weight: 500; color: #333; padding: .75rem 1rem; border-radius: .375rem; margin: .25rem .5rem; transition: all .3s ease; }
        .sidebar .nav-link:hover { color: #198754; background-color: rgba(25,135,84,.1); }
        .sidebar .nav-link.active { color: #198754; background-color: rgba(25,135,84,.15); border-left: 3px solid #198754; }
        @media (min-width: 768px) { main { margin-left: 200px; } }
        main { margin-top: 56px; padding-top: 1rem; }
        .card { border: none; border-radius: .5rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
        .badge { font-size: 0.8rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-seedling me-2"></i>
            Toko Sederhana
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['nama_lengkap']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="penjualan.php"><i class="fas fa-cash-register me-2"></i>Transaksi Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link" href="pengeluaran.php"><i class="fas fa-money-bill-wave me-2"></i>Transaksi Pengeluaran</a></li>
                    <li class="nav-item"><a class="nav-link" href="produk.php"><i class="fas fa-box me-2"></i>Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="jadwal.php"><i class="fas fa-calendar-alt me-2"></i>Jadwal Pengambilan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="fas fa-chart-bar me-2"></i>Laporan</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detail Transaksi</h1>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Cetak</button>
                </div>
            </div>

            <!-- Informasi Transaksi -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Kode Transaksi:</strong></td>
                                    <td><?php echo $transaksi['kode_transaksi']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis:</strong></td>
                                    <td>
                                        <span class="badge <?php echo $transaksi['jenis'] == 'pemasukan' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($transaksi['jenis']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Kategori:</strong></td>
                                    <td><?php echo $transaksi['kategori_transaksi']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Deskripsi:</strong></td>
                                    <td><?php echo $transaksi['deskripsi']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong class="text-primary">Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
                                    <td><?php echo $transaksi['nama_user']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <?php if (!empty($detail_transaksi)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detail Produk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail_transaksi as $index => $detail): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $detail['kode_produk']; ?></td>
                                    <td><?php echo $detail['nama_produk']; ?></td>
                                    <td class="text-center"><?php echo $detail['kuantitas'] . ' ' . $detail['satuan']; ?></td>
                                    <td class="text-end">Rp <?php echo number_format($detail['harga_satuan'], 0, ',', '.'); ?></td>
                                    <td class="text-end"><strong>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="5" class="text-end">TOTAL:</th>
                                    <th class="text-end">Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada detail produk untuk transaksi ini</h5>
                    <p class="text-muted">Transaksi ini mungkin tidak melibatkan produk atau detail belum tercatat.</p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


