<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
// Tampilkan halaman login langsung di index
require_once 'auth/login.php';
?> 

