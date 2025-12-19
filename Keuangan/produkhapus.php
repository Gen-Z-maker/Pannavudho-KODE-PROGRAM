<?php
session_start();
require_once 'config/database.php';

// Cek login
if (empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$db = (new Database())->getConnection();

// Hapus produk
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    try {
        $stmt = $db->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Produk berhasil dihapus.";
    } catch (PDOException $e) {
        $msg = "Gagal menghapus produk: " . $e->getMessage();
    }

    header("Location: produk.php?msg=" . urlencode($msg));
    exit;
}

// Jika tidak ada aksi hapus, arahkan ke produk.php
header("Location: produk.php");
exit;
?>