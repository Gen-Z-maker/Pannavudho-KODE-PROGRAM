<?php
session_start();
require_once 'config/database.php';

// --- Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// --- Koneksi DB
$database = new Database();
$db = $database->getConnection();

// --- Filter periode (format YYYY-MM)
$bulan = isset($_GET['bulan']) && !empty($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Validasi format bulan sederhana
if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
    $bulan = date('Y-m');
}

// --- Query: Ringkasan Keuangan (periode terpilih)
$query = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN total ELSE 0 END), 0) AS total_pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN total ELSE 0 END), 0) AS total_pengeluaran,
        COALESCE(COUNT(*), 0) AS total_transaksi
    FROM transaksi
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$laporan_keuangan = $stmt->fetch(PDO::FETCH_ASSOC);

$total_pemasukan = isset($laporan_keuangan['total_pemasukan']) ? (int)$laporan_keuangan['total_pemasukan'] : 0;
$total_pengeluaran = isset($laporan_keuangan['total_pengeluaran']) ? (int)$laporan_keuangan['total_pengeluaran'] : 0;
$keuntungan = $total_pemasukan - $total_pengeluaran;
$total_transaksi = isset($laporan_keuangan['total_transaksi']) ? (int)$laporan_keuangan['total_transaksi'] : 0;

// --- Query: Transaksi per Kategori (periode bulanan)
$query = "
    SELECT
        IFNULL(kategori_transaksi, 'Tanpa Kategori') AS kategori_transaksi,
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN total ELSE 0 END), 0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN total ELSE 0 END), 0) AS pengeluaran
    FROM transaksi
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
    GROUP BY kategori_transaksi
    ORDER BY pemasukan - pengeluaran DESC
";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$transaksi_kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- Query: Produk Terlaris (hanya transaksi pemasukan di periode terpilih)
$query = "
    SELECT
        p.id,
        p.nama_produk,
        p.kode_produk,
        p.stok,
        p.harga_jual,
        COALESCE(SUM(dt.kuantitas), 0) AS total_terjual,
        COALESCE(SUM(dt.subtotal), 0) AS total_pendapatan
    FROM produk p
    JOIN detail_transaksi dt ON p.id = dt.produk_id
    JOIN transaksi t ON dt.transaksi_id = t.id
        AND DATE_FORMAT(t.tanggal, '%Y-%m') = ?
        AND t.jenis = 'pemasukan'
    GROUP BY p.id
    ORDER BY total_terjual DESC
    LIMIT 10
";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$produk_terlaris = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Query: Grafik bulanan (periode terpilih) -> memberikan 1 titik data
$query = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN total END), 0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN total END), 0) AS pengeluaran
    FROM transaksi
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$grafik_bulanan = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$grafik_bulanan) {
    $grafik_bulanan = ['pemasukan' => 0, 'pengeluaran' => 0];
}

// --- Query: Stok produk (menampilkan stok saat ini, tidak difilter periode)
$query = "
    SELECT
        p.nama_produk,
        p.kode_produk,
        p.stok,
        p.satuan,
        p.harga_jual,
        k.nama_kategori,
        p.status
    FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    ORDER BY p.stok ASC
";
$stmt = $db->prepare($query);
$stmt->execute();
$stok_produk = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Manajemen Keuangan Toko Sederhana</title>
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
    <!-- Navbar (tetap sama UI) -->
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
                            <?php echo htmlentities($_SESSION['nama_lengkap']); ?>
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
                            <a class="nav-link" href="transaksi.php">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Transaksi Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="transaksi.php?jenis=pengeluaran">
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Laporan Keuangan</h1>
                        <a href="cetak_laporan.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
                             class="btn btn-danger">
                         <i class="fa fa-file-pdf"></i> Cetak PDF
                        </a>
                </div>

                <!-- Filter Periode -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="bulan" class="form-label">Periode</label>
                                <input type="month" class="form-control" id="bulan" name="bulan"
                                       value="<?php echo htmlentities($bulan); ?>" onchange="this.form.submit()">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <a href="laporan.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ringkasan Keuangan -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 report-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Pemasukan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?>
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
                        <div class="card border-left-success shadow h-100 py-2 report-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Pengeluaran</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?>
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
                        <div class="card border-left-info shadow h-100 py-2 report-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Keuntungan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Rp <?php echo number_format($keuntungan, 0, ',', '.'); ?>
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
                        <div class="card border-left-warning shadow h-100 py-2 report-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Transaksi</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_transaksi; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik & Kategori -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Grafik Keuangan Periode: <?php echo date('F Y', strtotime($bulan . '-01')); ?></h6>
                            </div>
                            <div class="card-body" style="height: 260px;">
                                <canvas id="monthlyChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Transaksi per Kategori (<?php echo date('F Y', strtotime($bulan . '-01')); ?>)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Kategori</th>
                                                <th class="text-end">Pemasukan</th>
                                                <th class="text-end">Pengeluaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                             <?php if (!empty($transaksi_kategori)): ?>
                                                 <?php foreach ($transaksi_kategori as $kategori): ?>
                                                     <tr>
                                                         <td>
                                                             <a href="detail_transaksi.php?kategori=<?php echo urlencode($kategori['kategori_transaksi']); ?>&bulan=<?php echo $bulan; ?>" class="text-decoration-none">
                                                                 <?php echo htmlentities($kategori['kategori_transaksi']); ?>
                                                             </a>
                                                         </td>
                                                         <td class="text-success text-end">Rp <?php echo number_format($kategori['pemasukan'], 0, ',', '.'); ?></td>
                                                         <td class="text-danger text-end">Rp <?php echo number_format($kategori['pengeluaran'], 0, ',', '.'); ?></td>
                                                     </tr>
                                                 <?php endforeach; ?>
                                             <?php else: ?>
                                                 <tr>
                                                     <td colspan="3" class="text-center">Tidak ada data untuk periode ini.</td>
                                                 </tr>
                                             <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Terlaris -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Produk Terlaris (<?php echo date('F Y', strtotime($bulan . '-01')); ?>)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Kode</th>
                                                <th>Nama Produk</th>
                                                <th>Stok</th>
                                                <th>Terjual</th>
                                                <th class="text-end">Pendapatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($produk_terlaris)): ?>
                                                <?php foreach ($produk_terlaris as $index => $produk): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge <?php echo $index < 3 ? 'bg-warning' : 'bg-secondary'; ?>">
                                                                #<?php echo $index + 1; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlentities($produk['kode_produk']); ?></td>
                                                        <td><?php echo htmlentities($produk['nama_produk']); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $produk['stok'] > 10 ? 'bg-success' : ($produk['stok'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                                <?php echo $produk['stok']; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $produk['total_terjual']; ?></td>
                                                        <td class="text-end">Rp <?php echo number_format($produk['total_pendapatan'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada data penjualan untuk periode ini.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stok Produk -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Status Stok Produk</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Produk</th>
                                                <th>Kategori</th>
                                                <th>Stok</th>
                                                <th>Harga Jual</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($stok_produk)): ?>
                                                <?php foreach ($stok_produk as $produk): ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($produk['kode_produk']); ?></td>
                                                        <td><?php echo htmlentities($produk['nama_produk']); ?></td>
                                                        <td>
                                                            <?php echo htmlentities($produk['nama_kategori']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $produk['stok'] > 10 ? 'bg-success' : ($produk['stok'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                                <?php echo $produk['stok'] . ' ' . htmlentities($produk['satuan']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end">Rp <?php echo number_format($produk['harga_jual'], 0, ',', '.'); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $produk['status'] == 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                                <?php echo ucfirst(htmlentities($produk['status'])); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada data produk.</td>
                                                </tr>
                                            <?php endif; ?>
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

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data grafik dari PHP (periode terpilih)
        const pemasukan = <?php echo (int)$grafik_bulanan['pemasukan']; ?>;
        const pengeluaran = <?php echo (int)$grafik_bulanan['pengeluaran']; ?>;
        const periodeLabel = "<?php echo date('F Y', strtotime($bulan . '-01')); ?>";

        // Monthly Chart (bar chart, 1 periode)
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: [periodeLabel],
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: [pemasukan],
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.15)',
                            order: 1
                        },
                        {
                            label: 'Pengeluaran',
                            data: [pengeluaran],
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.15)',
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed.y;
                                    return context.dataset.label + ': Rp ' + val.toLocaleString('id-ID');
                                }
                            }
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

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>