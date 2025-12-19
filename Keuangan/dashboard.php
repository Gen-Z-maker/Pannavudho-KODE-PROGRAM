<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil data untuk dashboard
$current_month = date('Y-m');
$current_year = date('Y');

// Total pendapatan bulan ini
$query = "SELECT COALESCE(SUM(total), 0) as total_pendapatan 
          FROM transaksi 
          WHERE jenis = 'pemasukan' 
          AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
$stmt = $db->prepare($query);
$stmt->execute([$current_month]);
$pendapatan_bulan = $stmt->fetch(PDO::FETCH_ASSOC)['total_pendapatan'];

// Total pengeluaran bulan ini
$query = "SELECT COALESCE(SUM(total), 0) as total_pengeluaran 
          FROM transaksi 
          WHERE jenis = 'pengeluaran' 
          AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
$stmt = $db->prepare($query);
$stmt->execute([$current_month]);
$pengeluaran_bulan = $stmt->fetch(PDO::FETCH_ASSOC)['total_pengeluaran'];

// Keuntungan bulan ini
$keuntungan_bulan = $pendapatan_bulan - $pengeluaran_bulan;

// Total stok produk
$query = "SELECT COUNT(*) as total_produk, COALESCE(SUM(stok), 0) as total_stok 
          FROM produk WHERE status = 'aktif'";
$stmt = $db->prepare($query);
$stmt->execute();
$produk_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Transaksi terbaru
$query = "SELECT t.*, u.nama_lengkap as nama_user
          FROM transaksi t
          LEFT JOIN users u ON t.user_id = u.id
          ORDER BY t.tanggal DESC
          LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$transaksi_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen Keuangan Toko Sederhana</title>
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
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #198754 !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #0dcaf0 !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #ffc107 !important;
        }
        .text-xs {
            font-size: 0.7rem;
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Penjualan.php">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Transaksi Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Pengeluaran.php">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Transaksi Pengeluaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="produk.php">
                                <i class="fas fa-box me-2"></i>
                                Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="jadwal.php">
                                <i class="fas fa-box me-2"></i>
                                Jadwal Pengambilan
                            </a>
                        </li>                        
                        <li class="nav-item">
                            <a class="nav-link" href="laporan.php">
                                <i class="fas fa-chart-bar me-2"></i>
                                Laporan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Pendapatan (Bulan Ini)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($pendapatan_bulan, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pengeluaran (Bulan Ini)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($pengeluaran_bulan, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Keuntungan (Bulan Ini)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($keuntungan_bulan, 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Produk</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $produk_info['total_produk']; ?> Item
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Ringkasan Keuangan</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td>Total Pendapatan</td>
                                        <td class="text-end text-success">Rp <?php echo number_format($pendapatan_bulan, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Pengeluaran</td>
                                        <td class="text-end text-danger">Rp <?php echo number_format($pengeluaran_bulan, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Keuntungan Bersih</td>
                                        <td class="text-end text-primary">Rp <?php echo number_format($keuntungan_bulan, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Margin Keuntungan</td>
                                        <td class="text-end text-info">
                                            <?php echo $pendapatan_bulan > 0 ? number_format(($keuntungan_bulan / $pendapatan_bulan) * 100, 2) : 0; ?>%
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Transaksi Terbaru</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kode</th>
                                                <th>Jenis</th>
                                                <th>Deskripsi</th>
                                                <th>Total</th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transaksi_terbaru as $transaksi): ?>
                                             <tr>
                                                 <td><?php echo date('d/m/Y', strtotime($transaksi['tanggal'])); ?></td>
                                                 <td>
                                                     <a href="detail_transaksi.php?id=<?php echo $transaksi['id']; ?>" class="text-decoration-none">
                                                         <?php echo $transaksi['kode_transaksi']; ?>
                                                     </a>
                                                 </td>
                                                 <td>
                                                     <span class="badge <?php echo $transaksi['jenis'] == 'pemasukan' ? 'bg-success' : 'bg-danger'; ?>">
                                                         <?php echo ucfirst($transaksi['jenis']); ?>
                                                     </span>
                                                 </td>
                                                 <td><?php echo $transaksi['deskripsi']; ?></td>
                                                 <td>Rp <?php echo number_format($transaksi['total'], 0, ',', '.'); ?></td>
                                                 <td><?php echo $transaksi['nama_user']; ?></td>
                                             </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart data dari PHP
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => {
                        const date = new Date(item.bulan + '-01');
                        return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Pendapatan',
                        data: chartData.map(item => parseInt(item.pendapatan)),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Pengeluaran',
                        data: chartData.map(item => parseInt(item.pengeluaran)),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html> 

