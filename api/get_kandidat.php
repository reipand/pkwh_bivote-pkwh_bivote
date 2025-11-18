<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan user sudah login dan mengarahkan ke halaman ini
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

$kandidat = [];
// PERBAIKAN: Mengambil kolom-kolom yang sesuai dengan skema database
$sql = "SELECT id, nama_lengkap, nis, visi, misi, video_path, foto_path, kejar, usia FROM kandidat ORDER BY id ASC";
$result = $koneksi->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // PERBAIKAN: Mengkonversi string misi menjadi array
        $row['misi'] = explode(';', $row['misi']);
        $kandidat[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $kandidat]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data kandidat.']);
}

$koneksi->close();
?>