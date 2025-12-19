<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

require_once('fpdf/fpdf.php');

$database = new Database();
$db = $database->getConnection();

// Filter pencarian (opsional)
$nama = isset($_GET['nama']) ? trim($_GET['nama']) : '';
$params = [];
$where = '';

if ($nama !== '') {
    $where = "WHERE p.nama_produk LIKE ?";
    $params[] = "%$nama%";
}

$query = "SELECT j.*, p.nama_produk 
          FROM jadwal_pengambilan j
          LEFT JOIN produk p ON j.produk_id = p.id
          $where
          ORDER BY j.tanggal DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= PDF =================
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(10,10,10);

// Judul
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Laporan Jadwal Pengambilan Pupuk',0,1,'C');
$pdf->Ln(3);

// Header tabel
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,8,'Tanggal',1,0,'C');
$pdf->Cell(40,8,'Nama Produk',1,0,'C');
$pdf->Cell(25,8,'Jumlah',1,0,'C');
$pdf->Cell(45,8,'Deskripsi',1,0,'C');
$pdf->Cell(50,8,'Alamat',1,1,'C');

// Isi tabel
$pdf->SetFont('Arial','',9);

if (empty($data)) {
    $pdf->Cell(190,8,'Tidak ada data',1,1,'C');
} else {
    foreach ($data as $row) {
        $pdf->Cell(30,8,date('d/m/Y', strtotime($row['tanggal'])),1);
        $pdf->Cell(40,8,$row['nama_produk'],1);
        $pdf->Cell(25,8,$row['jumlah'].' '.$row['satuan'],1);

        // Deskripsi
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(45,8,$row['deskripsi'],1);
        $pdf->SetXY($x + 45, $y);

        // Alamat
        $pdf->MultiCell(50,8,$row['alamat'],1);
    }
}

$pdf->Output('I','Laporan_Jadwal_Pengambilan_Pupuk.pdf');
