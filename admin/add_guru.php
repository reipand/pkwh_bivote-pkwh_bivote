<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $nik = $_POST['nik'];
    $password = $_POST['password'];
    $jabatan = $_POST['jabatan'];

    // Validasi input
    if (empty($nama_lengkap) || empty($nik) || empty($password) || empty($jabatan)) {
        $_SESSION['error_message'] = 'Semua field harus diisi.';
        header("Location: manage_guru.php");
        exit;
    }

    // Cek apakah NIK sudah ada
    $stmt_check = $koneksi->prepare("SELECT id FROM guru WHERE nik = ?");
    $stmt_check->bind_param("s", $nik);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $_SESSION['error_message'] = 'NIK sudah terdaftar.';
        header("Location: manage_guru.php");
        exit;
    }
    $stmt_check->close();

    // Tambahkan guru baru
    $stmt = $koneksi->prepare("INSERT INTO guru (nama_lengkap, nik, password, jabatan, status_memilih) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $nama_lengkap, $nik, $password, $jabatan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Guru berhasil ditambahkan.';
    } else {
        $_SESSION['error_message'] = 'Gagal menambahkan guru.';
    }
    
    $stmt->close();
    $koneksi->close();
    
    header("Location: manage_guru.php");
    exit;
} else {
    header("Location: manage_guru.php");
    exit;
}
?>
