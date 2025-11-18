<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Hanya untuk admin.']);
    exit;
}

// Data yang akan dikirim dalam respons JSON
$response = ["status" => "success"];

// Query 1: Ambil semua hasil suara kandidat
$sql_results = "SELECT nama_lengkap, jumlah_suara, foto_path FROM kandidat ORDER BY jumlah_suara DESC";
$result_results = $koneksi->query($sql_results);

$all_results = [];
if ($result_results->num_rows > 0) {
    while($row = $result_results->fetch_assoc()) {
        $all_results[] = $row;
    }
}

// Menentukan Ketos dan Waketos
$response['ketos'] = $all_results[0] ?? null;
$response['waketos'] = $all_results[1] ?? null;
$response['all_results'] = $all_results;

// Query 2: Total Registered Voters dari tabel 'pemilih' dan 'guru'
$sql_voters_siswa = "SELECT COUNT(id) AS total_voters FROM pemilih";
$result_voters_siswa = $koneksi->query($sql_voters_siswa);
$row_voters_siswa = $result_voters_siswa->fetch_assoc();

$sql_voters_guru = "SELECT COUNT(id) AS total_voters FROM guru";
$result_voters_guru = $koneksi->query($sql_voters_guru);
$row_voters_guru = $result_voters_guru->fetch_assoc();

$response['total_voters_siswa'] = (int)$row_voters_siswa['total_voters'];
$response['total_voters_guru'] = (int)$row_voters_guru['total_voters'];
$response['total_voters'] = (int)$row_voters_siswa['total_voters'] + (int)$row_voters_guru['total_voters'];

// Query 3: Total Votes dari tabel 'pemilih' dan 'guru' (berdasarkan kolom status_memilih)
$sql_votes_siswa = "SELECT COUNT(status_memilih) AS total_votes FROM pemilih WHERE status_memilih = 1";
$result_votes_siswa = $koneksi->query($sql_votes_siswa);
$row_votes_siswa = $result_votes_siswa->fetch_assoc();

$sql_votes_guru = "SELECT COUNT(status_memilih) AS total_votes FROM guru WHERE status_memilih = 1";
$result_votes_guru = $koneksi->query($sql_votes_guru);
$row_votes_guru = $result_votes_guru->fetch_assoc();

$response['total_votes_siswa'] = (int)$row_votes_siswa['total_votes'];
$response['total_votes_guru'] = (int)$row_votes_guru['total_votes'];
$response['total_votes'] = (int)$row_votes_siswa['total_votes'] + (int)$row_votes_guru['total_votes'];

// Query 4: New Registrations (pendaftar baru hari ini)
$sql_registered_today = "SELECT COUNT(id) AS total FROM pemilih";
$result_registered = $koneksi->query($sql_registered_today);
$row_registered = $result_registered->fetch_assoc();
$response['total_registered'] = (int)$row_registered['total'];

// Kirim respons JSON
echo json_encode($response);

$koneksi->close();
?>