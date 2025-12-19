<?php
session_start();
require_once 'config/database.php';

// Cek login
if (empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$db = (new Database())->getConnection();

// --- Notifikasi ---
$message = $_GET['message'] ?? '';
$error   = $_GET['error'] ?? '';

// --- Ambil kategori ---
$kategori_list = $db->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll(PDO::FETCH_ASSOC);

// --- Filter & Pencarian ---
$search   = $_GET['search'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(p.kode_produk LIKE ? OR p.nama_produk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($kategori_filter) {
    $where[] = "p.kategori_id = ?";
    $params[] = $kategori_filter;
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// --- Ambil produk ---
$stmt = $db->prepare("
    SELECT p.*, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    $where_sql
    ORDER BY p.id DESC
");
$stmt->execute($params);
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Sistem Manajemen Keuangan Toko Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
       <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            width: 200px;
            min-width: 200px;
            max-width: 220px;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: #198754;
            background-color: rgba(25, 135, 84, 0.1);
        }
        .sidebar .nav-link.active {
            color: #198754;
            background-color: rgba(25, 135, 84, 0.15);
            border-left: 3px solid #198754;
        }
        @media (min-width: 768px) {
            main {
                margin-left: 200px;
            }
        }
        main {
            margin-top: 56px;
            padding-top: 1rem;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .report-card {
            transition: transform 0.2s;
        }
        .report-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-seedling me-2"></i> Toko Sederhana
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i> <?= $_SESSION['nama_lengkap']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
<div class="row">

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="pt-3">
        <ul class="nav flex-column">
            <li><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a class="nav-link" href="Penjualan.php"><i class="fas fa-exchange-alt me-2"></i>Transaksi Penjualan</a></li>
            <li><a class="nav-link" href="Pengeluaran.php"><i class="fas fa-exchange-alt me-2"></i>Transaksi Pengeluaran</a></li>
            <li><a class="nav-link active" href="produk.php"><i class="fas fa-box me-2"></i>Produk</a></li>
            <li><a class="nav-link" href="jadwal.php"><i class="fas fa-calendar-alt me-2"></i>Jadwal Pengambilan</a></li>
            <li><a class="nav-link" href="laporan.php"><i class="fas fa-chart-bar me-2"></i>Laporan</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Produk</h1>
        <a href="produktambah.php" class="btn btn-success"><i class="fas fa-plus me-2"></i>Tambah Produk</a>
    </div>

    <!-- Alert -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= $message; ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error; ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Produk</label>
                    <input type="text" name="search" value="<?= $search; ?>" class="form-control" placeholder="Kode / nama...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id']; ?>" <?= $kategori_filter == $kat['id'] ? 'selected' : ''; ?>>
                                <?= $kat['nama_kategori']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Cari</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="produk.php" class="btn btn-secondary w-100"><i class="fas fa-refresh me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Produk -->
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Daftar Produk</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Kode</th><th>Nama Produk</th><th>Kategori</th>
                        <th>Harga Beli</th><th>Harga Jual</th>
                        <th>Stok</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produk_list)): ?>
                        <tr><td colspan="8" class="text-center">Tidak ada data produk</td></tr>
                    <?php else: ?>
                        <?php foreach ($produk_list as $p): ?>
                            <tr>
                                <td><?= $p['kode_produk']; ?></td>
                                <td><?= $p['nama_produk']; ?></td>
                                <td><?= $p['nama_kategori']; ?></td>
                                <td>Rp <?= number_format($p['harga_beli'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $p['stok'] > 10 ? 'bg-success' : ($p['stok'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?= $p['stok'] . ' ' . $p['satuan']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $p['status'] == 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?= ucfirst($p['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="produkhapus.php?delete=<?= $p['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Hapus produk ini?')">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>