<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

// --- Ambil periode
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$database = new Database();
$db = $database->getConnection();

require('fpdf/fpdf.php');

// Query Ringkasan
$query = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis='pemasukan' THEN total END),0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis='pengeluaran' THEN total END),0) AS pengeluaran,
        COUNT(*) AS total_transaksi
    FROM transaksi
    WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$pemasukan = $data['pemasukan'];
$pengeluaran = $data['pengeluaran'];
$keuntungan = $pemasukan - $pengeluaran;

// Ambil semua transaksi (TANPA kolom keterangan)
$q2 = "SELECT tanggal, jenis, total 
       FROM transaksi 
       WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?
       ORDER BY tanggal ASC";

$stmt2 = $db->prepare($q2);
$stmt2->execute([$bulan]);
$transaksi = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// MULAI PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// HEADER
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Laporan Keuangan Toko Sederhana',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,7,'Periode: ' . date('F Y', strtotime($bulan . "-01")),0,1,'C');
$pdf->Ln(2);

// GARIS PEMISAH
$pdf->SetLineWidth(0.5);
$pdf->Line(10, 32, 200, 32);
$pdf->Ln(5);

// BAGIAN RINGKASAN
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Ringkasan Keuangan',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(60,7,'Total Pemasukan');
$pdf->Cell(0,7,': Rp '.number_format($pemasukan,0,',','.'),0,1);

$pdf->Cell(60,7,'Total Pengeluaran');
$pdf->Cell(0,7,': Rp '.number_format($pengeluaran,0,',','.'),0,1);

$pdf->Cell(60,7,'Keuntungan / Rugi');
$pdf->Cell(0,7,': Rp '.number_format($keuntungan,0,',','.'),0,1);

$pdf->Cell(60,7,'Jumlah Transaksi');
$pdf->Cell(0,7,': '.$data['total_transaksi'],0,1);

$pdf->Ln(5);

// TABEL TRANSAKSI
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Detail Transaksi',0,1);
$pdf->SetFont('Arial','B',10);

// HEADER TABEL
$pdf->SetFillColor(230,230,230);
$pdf->Cell(10,8,'No',1,0,'C',true);
$pdf->Cell(40,8,'Tanggal',1,0,'C',true);
$pdf->Cell(50,8,'Jenis',1,0,'C',true);
$pdf->Cell(50,8,'Jumlah',1,1,'C',true);

$pdf->SetFont('Arial','',10);
$no = 1;

foreach ($transaksi as $row) {
    $pdf->Cell(10,8,$no++,1,0,'C');
    $pdf->Cell(40,8,date('d-m-Y', strtotime($row['tanggal'])),1,0,'C');
    $pdf->Cell(50,8,ucfirst($row['jenis']),1,0,'C');
    $pdf->Cell(50,8,'Rp '.number_format($row['total'],0,',','.'),1,1,'R');
}

$pdf->Ln(5);

// FOOTER
$pdf->SetFont('Arial','I',9);
$pdf->Cell(0,10,'Dicetak pada '.date('d-m-Y H:i'),0,1,'R');

$pdf->Output();
exit;
?>
