<?php
date_default_timezone_set('Asia/Jakarta');
// Konfigurasi database (gunakan environment variables jika tersedia)
$host = getenv('DB_HOST') ?: 'localhost'; // default: localhost
$port = getenv('DB_PORT') ?: 3306; // default: 3306 (can be 3307 on host if docker maps to that)
$user = getenv('DB_USER') ?: 'reip';
$password = getenv('DB_PASSWORD') ?: 'bcst2526';
$database = getenv('DB_NAME') ?: 'db_pemilos';

// Jika host adalah 'localhost', mysqli will try to use a Unix socket which can trigger "Permission denied".
// To force a TCP connection (and avoid socket permission issues), convert 'localhost' to '127.0.0.1'.
if ($host === 'localhost') {
    $host = '127.0.0.1';
}

// Buat koneksi menggunakan mysqli (mode OOP). Pass port to force TCP connection when needed.
$koneksi = new mysqli($host, $user, $password, $database, (int)$port);

// Cek koneksi
if ($koneksi->connect_error) {
    // Tampilkan pesan error yang lebih deskriptif
    die("Koneksi gagal: " . htmlspecialchars($koneksi->connect_error));
}

// Set charset untuk mencegah masalah encoding
$koneksi->set_charset("utf8mb4");

// Jika perlu, tambahkan logging error ke file atau sistem lain
// Misalnya, untuk debugging:
// error_log("Database connected successfully", 3, "/path/to/your/error.log");
?>