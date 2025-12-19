<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle hapus
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM jadwal_pengambilan WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Jadwal berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus jadwal: " . $e->getMessage();
    }
}

// Redirect kembali ke jadwal.php dengan pesan
$redirect_url = "jadwal.php";
if ($message) {
    $redirect_url .= "?message=" . urlencode($message);
} elseif ($error) {
    $redirect_url .= "?error=" . urlencode($error);
}

header("Location: " . $redirect_url);
exit();
?>


