<?php
session_start();
require_once '../config/koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../page/login.php");
    exit;
}

// Ambil ID pengguna dari sesi
$user_id = $_SESSION['user_id'];
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'siswa';

// Periksa apakah pengguna sudah memberikan suara berdasarkan tipe user
if ($user_type === 'guru') {
    $stmt_check = $koneksi->prepare("SELECT status_memilih FROM guru WHERE id = ?");
} else {
    $stmt_check = $koneksi->prepare("SELECT status_memilih FROM pemilih WHERE id = ?");
}
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$voter = $result_check->fetch_assoc();
$stmt_check->close();

if ($voter['status_memilih'] == 1) {
    $_SESSION['vote_status'] = 'already_voted';
    header("Location: ../page/vote_confirmation.php");
    exit;
}

// Ambil ID kandidat dari parameter GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['vote_status'] = 'no_candidate';
    header("Location: ../page/dashboard.php");
    exit;
}

$candidate_id = (int)$_GET['id'];

// Mulai transaksi untuk memastikan konsistensi data
$koneksi->begin_transaction();

try {
    // Perbarui jumlah suara kandidat
    $stmt_update_candidate = $koneksi->prepare("UPDATE kandidat SET jumlah_suara = jumlah_suara + 1 WHERE id = ?");
    $stmt_update_candidate->bind_param("i", $candidate_id);

    // Perbarui status vote pengguna berdasarkan tipe user
    if ($user_type === 'guru') {
        $stmt_update_voter = $koneksi->prepare("UPDATE guru SET status_memilih = 1, id_kandidat_dipilih = ? WHERE id = ?");
        $stmt_update_voter->bind_param("ii", $candidate_id, $user_id);
    } else {
        $stmt_update_voter = $koneksi->prepare("UPDATE pemilih SET status_memilih = 1, id_kandidat_dipilih = ? WHERE id = ?");
        $stmt_update_voter->bind_param("ii", $candidate_id, $user_id);
    }
    
    // Jalankan kedua kueri
    if ($stmt_update_candidate->execute() && $stmt_update_voter->execute()) {
        $koneksi->commit();
        $_SESSION['vote_status'] = 'success';
    } else {
        $koneksi->rollback();
        $_SESSION['vote_status'] = 'failed';
    }

    $stmt_update_candidate->close();
    $stmt_update_voter->close();
    
} catch (Exception $e) {
    $koneksi->rollback();
    $_SESSION['vote_status'] = 'failed';
}

$koneksi->close();

header("Location: ../page/vote_confirmation.php");
exit;