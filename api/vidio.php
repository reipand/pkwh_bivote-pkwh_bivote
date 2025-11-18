<?php
// Izinkan akses dari origin lain (jika diperlukan untuk pengujian)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Periksa metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Metode request tidak diizinkan."]);
    exit;
}

require_once '../config/koneksi.php';

if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Koneksi database gagal: " . $koneksi->connect_error]);
    exit;
}

if (!isset($_FILES['video']) || !isset($_POST['candidate_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Data tidak lengkap."]);
    exit;
}

$candidate_id = $_POST['candidate_id'];
$video_file = $_FILES['video'];

$allowed_types = ['video/mp4', 'video/webm', 'video/mov'];
if (!in_array($video_file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(["message" => "Format video tidak didukung."]);
    exit;
}

$upload_dir = __DIR__ . "/../assets/videos/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$original_filename = $video_file['name'];
$extension = pathinfo($original_filename, PATHINFO_EXTENSION);
$new_filename = 'video_' . $candidate_id . '_' . time() . '.' . $extension;
$temp_path = $video_file['tmp_name'];
$final_path = $upload_dir . $new_filename;

if (!move_uploaded_file($temp_path, $final_path)) {
    http_response_code(500);
    echo json_encode(["message" => "Gagal memindahkan file yang diunggah."]);
    exit;
}

$compressed_filename = 'compressed_' . $new_filename;
$compressed_path = $upload_dir . $compressed_filename;

$command = "ffmpeg -i " . escapeshellarg($final_path) . " -vcodec libx264 -crf 28 " . escapeshellarg($compressed_path) . " 2>&1";

$output = shell_exec($command);

if (!file_exists($compressed_path)) {
    // Jika kompresi gagal, hapus file asli dan kirim pesan error
    unlink($final_path);
    http_response_code(500);
    echo json_encode(["message" => "Kompresi video gagal.", "output" => $output]);
    exit;
}

// Hapus file asli yang tidak dikompresi
unlink($final_path);

// Simpan URL video ke database
$video_url = '../assets/videos/' . $compressed_filename;

// Perbaikan di bagian ini
// Menggunakan tabel `kandidat` dan kolom `video_path`
$stmt = $koneksi->prepare("UPDATE kandidat SET video_path = ? WHERE id = ?");
$stmt->bind_param("si", $video_url, $candidate_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Video berhasil diunggah dan dikompresi.", "url" => $video_url]);
} else {
    // Jika gagal menyimpan ke DB, hapus juga file yang dikompres
    unlink($compressed_path);
    http_response_code(500);
    echo json_encode(["message" => "Gagal menyimpan URL video ke database."]);
}

$stmt->close();
$koneksi->close();
?>