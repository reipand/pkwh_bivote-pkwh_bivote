<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['candidate_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Bad request"]);
    exit;
}

$candidate_id = (int)$_POST['candidate_id'];

// Ambil data kandidat
$stmt = $koneksi->prepare("SELECT video_path FROM kandidat WHERE id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
$kandidat = $result->fetch_assoc();
$stmt->close();

if (!$kandidat) {
    http_response_code(404);
    echo json_encode(["message" => "Kandidat tidak ditemukan"]);
    exit;
}

// Hapus file video jika ada
if ($kandidat['video_path'] && file_exists('../' . $kandidat['video_path'])) {
    if (unlink('../' . $kandidat['video_path'])) {
        // Update database
        $stmt = $koneksi->prepare("UPDATE kandidat SET video_path = NULL WHERE id = ?");
        $stmt->bind_param("i", $candidate_id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Video berhasil dihapus"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal menghapus video dari database"]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Gagal menghapus file video"]);
    }
} else {
    // Jika file tidak ada, tetap update database
    $stmt = $koneksi->prepare("UPDATE kandidat SET video_path = NULL WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Video berhasil dihapus dari database"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Gagal menghapus video dari database"]);
    }
    $stmt->close();
}

$koneksi->close();
?>