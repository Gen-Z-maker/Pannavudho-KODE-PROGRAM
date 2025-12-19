<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Pencarian
$alamat = isset($_GET['alamat']) ? trim($_GET['alamat']) : '';
$params = [];
$where = '';

if ($alamat !== '') {
    $where = "WHERE j.alamat LIKE ?";
    $params[] = "%$alamat%";
}

// Ambil data jadwal
$query = "SELECT j.*, p.nama_produk 
          FROM jadwal_pengambilan j 
          LEFT JOIN produk p ON j.produk_id = p.id 
          $where 
          ORDER BY j.tanggal DESC, j.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$jadwal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pengambilan Pupuk - Toko Sederhana</title>
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
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-2px);
        }
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
                        <li class="nav-item">
                            <a class="nav-link" href="dashboardkaryawan.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Penjualankaryawan.php">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Transaksi Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Pengeluarankaryawan.php">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Transaksi Pengeluaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="produkkaryawan.php">
                                <i class="fas fa-box me-2"></i>
                                Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="jadwalkaryawan.php">
                                <i class="fas fa-box me-2"></i>
                                Jadwal Pengambilan
                            </a>
                        </li>                        
                    </ul>
                </div>
            </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Jadwal Pengambilan Pupuk</h1>
                            <a href="jadwalkartambah.php" type="button" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tambah Jadwal
                        </a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form class="row g-3" method="GET">
                        <div class="col-md-6">
                            <label class="form-label">Cari Jadwal</label>
                            <input type="text" class="form-control" name="alamat" placeholder="alamat" value="<?php echo $alamat; ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button class="btn btn-primary me-2" type="submit"><i class="fas fa-search me-1"></i>Cari</button>
                            <a href="jadwalkaryawan.php" class="btn btn-secondary"><i class="fas fa-rotate me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Jadwal</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Produk</th>
                                    <th>Jumlah</th>
                                    <th>Deskripsi</th>
                                    <th>Alamat</th>
                                    <th width="90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwal_list)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada jadwal</td></tr>
                                <?php else: foreach ($jadwal_list as $row): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                        <td><?php echo $row['nama_produk']; ?></td>
                                        <td><?php echo $row['jumlah'] . ' ' . $row['satuan']; ?></td>
                                        <td><?php echo $row['deskripsi']; ?></td>
                                        <td><?php echo $row['alamat']; ?></td>
                                        <td>
                                            <a href="jadwalhapus.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus jadwal ini?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>