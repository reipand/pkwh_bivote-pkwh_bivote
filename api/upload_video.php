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

$candidate_id = (int)$_POST['candidate_id'];

// Validasi kandidat ada di database
$stmt_check = $koneksi->prepare("SELECT id FROM kandidat WHERE id = ?");
$stmt_check->bind_param("i", $candidate_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["message" => "Kandidat dengan ID $candidate_id tidak ditemukan."]);
    exit;
}
$stmt_check->close();

$video_file = $_FILES['video'];

// Debug logging
error_log("Video upload attempt - Candidate ID: $candidate_id, File: " . $video_file['name'] . ", Size: " . $video_file['size'] . ", Type: " . $video_file['type']);

// Validasi file upload
if ($video_file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize di php.ini)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE dalam form)',
        UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang di-upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary tidak ada',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
    ];
    
    $error_msg = isset($error_messages[$video_file['error']]) ? $error_messages[$video_file['error']] : 'Error upload tidak diketahui: ' . $video_file['error'];
    
    http_response_code(400);
    echo json_encode(["message" => "Error upload file: " . $error_msg]);
    exit;
}

// Validasi ukuran file (max 50MB)
if ($video_file['size'] > 50 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(["message" => "Ukuran file terlalu besar. Maksimal 50MB."]);
    exit;
}

// Validasi tipe file video menggunakan finfo untuk akurasi yang lebih baik
$allowed_video_types = ['video/mp4', 'video/webm', 'video/quicktime', 'video/avi', 'video/x-msvideo', 'video/x-mov', 'video/mov'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $video_file['tmp_name']);
finfo_close($finfo);

// Cek MIME type yang dideteksi
if (!in_array($mime_type, $allowed_video_types)) {
    // Jika MIME type tidak cocok, coba cek ekstensi file sebagai fallback
    $allowed_extensions = ['mp4', 'webm', 'mov', 'avi'];
    $file_extension = strtolower(pathinfo($video_file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        http_response_code(400);
        echo json_encode([
            "message" => "Format video tidak didukung. Format yang didukung: MP4, WebM, MOV, AVI.", 
            "detected_mime" => $mime_type,
            "file_extension" => $file_extension,
            "file_name" => $video_file['name']
        ]);
        exit;
    }
}

$upload_dir = __DIR__ . "/../assets/videos/";
error_log("Upload directory: $upload_dir");

if (!is_dir($upload_dir)) {
    error_log("Creating upload directory: $upload_dir");
    if (!mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(["message" => "Gagal membuat direktori upload."]);
        exit;
    }
}

// Bersihkan filename
$original_filename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $video_file['name']);
$extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

// Pastikan ekstensi valid
$valid_extensions = ['mp4', 'webm', 'mov', 'avi'];
if (!in_array($extension, $valid_extensions)) {
    http_response_code(400);
    echo json_encode(["message" => "Ekstensi file tidak valid: $extension. Ekstensi yang didukung: " . implode(', ', $valid_extensions)]);
    exit;
}

$new_filename = 'video_' . $candidate_id . '_' . time() . '.' . $extension;
$temp_path = $video_file['tmp_name'];
$final_path = $upload_dir . $new_filename;

error_log("Original filename: " . $video_file['name']);
error_log("Clean filename: $original_filename");
error_log("New filename: $new_filename");
error_log("Temp path: $temp_path");
error_log("Final path: $final_path");

// Pindahkan file yang diunggah
if (!move_uploaded_file($temp_path, $final_path)) {
    $error = error_get_last();
    error_log("Failed to move uploaded file. Last error: " . print_r($error, true));
    http_response_code(500);
    echo json_encode(["message" => "Gagal memindahkan file yang diunggah. Error: " . ($error ? $error['message'] : 'Unknown error')]);
    exit;
}

error_log("File successfully moved to: $final_path");

// Cek apakah FFMPEG tersedia
$ffmpeg_available = false;
$ffmpeg_check = shell_exec('which ffmpeg');
if (!empty($ffmpeg_check)) {
    $ffmpeg_available = true;
}

$compressed_filename = $new_filename; // Default gunakan file asli
$compressed_path = $final_path;

// Kompresi video jika FFMPEG tersedia
if ($ffmpeg_available) {
    $compressed_filename = 'compressed_' . $new_filename;
    $compressed_path = $upload_dir . $compressed_filename;
    
    // Kompres video dengan kualitas yang lebih baik
    $command = "ffmpeg -i " . escapeshellarg($final_path) . " -vcodec libx264 -crf 23 -preset medium -acodec aac -b:a 128k " . escapeshellarg($compressed_path) . " 2>&1";
    
    $output = shell_exec($command);
    
    if (file_exists($compressed_path) && filesize($compressed_path) > 0) {
        // Hapus file asli yang tidak dikompresi
        unlink($final_path);
        $video_filename = $compressed_filename;
    } else {
        // Jika kompresi gagal, gunakan file asli
        $compressed_filename = $new_filename;
        $compressed_path = $final_path;
        error_log("Kompresi video gagal: " . $output);
    }
} else {
    $video_filename = $new_filename;
}

// Simpan URL video ke database
$video_url = 'assets/videos/' . $compressed_filename;

// Update database
$stmt = $koneksi->prepare("UPDATE kandidat SET video_path = ? WHERE id = ?");
$stmt->bind_param("si", $video_url, $candidate_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        "message" => "Video berhasil diunggah" . ($ffmpeg_available ? " dan dikompresi" : "") . ".",
        "url" => $video_url,
        "compressed" => $ffmpeg_available
    ]);
} else {
    // Jika gagal menyimpan ke DB, hapus file
    if (file_exists($compressed_path)) {
        unlink($compressed_path);
    }
    http_response_code(500);
    echo json_encode(["message" => "Gagal menyimpan URL video ke database: " . $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>