<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
$database = new Database();
$db = $database->getConnection();
// Ambil produk aktif
$stmt = $db->prepare("SELECT id, nama_produk, harga_jual, stok FROM produk WHERE status = 'aktif' ORDER BY nama_produk");
$stmt->execute();
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tipe_transaksi']) && $_POST['tipe_transaksi'] == 'penjualan') {
        $tanggal = $_POST['tanggal'];
        $deskripsi = $_POST['deskripsi'];
        $total = $_POST['total'];
        $user_id = $_SESSION['user_id'];
        $kode_transaksi = 'TRX' . date('YmdHis');
        try {
            $db->beginTransaction();
            $produk_id = $_POST['produk_id'];
            $qty = $_POST['qty'];
            $harga = $_POST['harga'];
            $stmt = $db->prepare("INSERT INTO transaksi (kode_transaksi, tanggal, jenis, kategori_transaksi, deskripsi, total, user_id) VALUES (?, ?, 'pemasukan', 'Penjualan', ?, ?, ?)");
            $stmt->execute([$kode_transaksi, $tanggal, $deskripsi, $total, $user_id]);
            $transaksi_id = $db->lastInsertId();
            for ($i = 0; $i < count($produk_id); $i++) {
                $pid = $produk_id[$i];
                $q = $qty[$i];
                $h = $harga[$i];
                $subtotal = $q * $h;
                $stmt = $db->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, kuantitas, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$transaksi_id, $pid, $q, $h, $subtotal]);
                $stmt = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
                $stmt->execute([$q, $pid]);
            }
            $db->commit();
            $message = "Transaksi penjualan berhasil disimpan!";
        } catch (PDOException $e) {
            $db->rollBack();
            $error = "Gagal simpan transaksi: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan - Sistem Manajemen Keuangan Toko Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; width: 200px; min-width: 200px; max-width: 220px; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); background-color: #f8f9fa; }
        .sidebar .nav-link { font-weight: 500; color: #333; padding: 0.75rem 1rem; border-radius: 0.375rem; margin: 0.25rem 0.5rem; transition: all 0.3s ease; }
        .sidebar .nav-link:hover { color: #198754; background-color: rgba(25, 135, 84, 0.1); }
        .sidebar .nav-link.active { color: #198754; background-color: rgba(25, 135, 84, 0.15); border-left: 3px solid #198754; }
        @media (min-width: 768px) { main { margin-left: 200px; } }
        main { margin-top: 56px; padding-top: 1rem; }
        .navbar-brand { font-size: 1.5rem; font-weight: bold; letter-spacing: 1px; }
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
            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Transaksi Penjualan</h1>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" id="formPenjualan">
                    <input type="hidden" name="tipe_transaksi" value="penjualan">
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi transaksi" required>
                    </div>
                    <div id="produkArea">
                        <label>Produk Terjual</label>
                        <div class="row mb-2 produk-item">
                            <div class="col-md-5">
                                <select name="produk_id[]" class="form-select produk-select" required>
                                    <option value="">Pilih Produk</option>
                                    <?php foreach ($produk_list as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" data-harga="<?php echo $p['harga_jual']; ?>" data-stok="<?php echo $p['stok']; ?>">
                                            <?php echo $p['nama_produk'] . ' (Stok: ' . $p['stok'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="qty[]" class="form-control qty-input" min="1" placeholder="Qty" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="harga[]" class="form-control harga-input" min="0" placeholder="Harga" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-remove-produk">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="btnTambahProduk">Tambah Produk</button>
                    <div class="mb-3">
                        <label>Total (Rp)</label>
                        <input type="number" name="total" id="total" class="form-control" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Penjualan: tambah/hapus produk
    $('#btnTambahProduk').click(function(){
        var item = $('.produk-item').first().clone();
        item.find('select, input').val('');
        $('#produkArea').append(item);
    });
    $(document).on('click', '.btn-remove-produk', function(){
        if ($('.produk-item').length > 1) $(this).closest('.produk-item').remove();
    });
    $(document).on('change', '.produk-select', function(){
        var harga = $(this).find(':selected').data('harga');
        $(this).closest('.produk-item').find('.harga-input').val(harga);
    });
    $(document).on('input change', '.qty-input, .harga-input', function(){
        var total = 0;
        $('.produk-item').each(function(){
            var qty = parseFloat($(this).find('.qty-input').val()) || 0;
            var harga = parseFloat($(this).find('.harga-input').val()) || 0;
            total += qty * harga;
        });
        $('#total').val(total);
    });
    </script>
</body>
</html>
