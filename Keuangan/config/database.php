<?php
// Konfigurasi Database MySQL untuk Sistem Manajemen Keuangan Toko Pupuk

class Database {
    private $host = "localhost";
    private $db_name = "toko_pupuk_db";
    private $username = "root";
    private $password = "";
    public $conn;

    // Mendapatkan koneksi database
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    // Menutup koneksi database
    public function closeConnection() {
        $this->conn = null;
    }
}
?> 



