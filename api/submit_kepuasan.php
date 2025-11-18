<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => 'Terjadi kesalahan tidak terduga.'
];

// Periksa apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data yang dikirim dari form
    $sesi = $_POST['sesi'] ?? null;
    $id_kandidat = $_POST['id_kandidat'] ?? null;
    $nilai_kepuasan = $_POST['nilai_kepuasan'] ?? null;

    // Validasi data
    if ($sesi === null || $id_kandidat === null || $nilai_kepuasan === null) {
        $response['message'] = 'Data tidak lengkap. Harap pilih kandidat dan tingkat kepuasan.';
        echo json_encode($response);
        exit;
    }

    // Periksa apakah user sudah memberikan penilaian di sesi ini
    // Menggunakan variabel sesi PHP untuk mencegah duplikasi
    $session_key = 'debat_sesi_' . $sesi . '_voted';
    if (isset($_SESSION[$session_key]) && $_SESSION[$session_key] === true) {
        $response['message'] = 'Anda sudah memberikan penilaian untuk sesi ini.';
        echo json_encode($response);
        exit;
    }

    // Siapkan query SQL untuk menyimpan data ke tabel `kepuasan`
    $sql = "INSERT INTO kepuasan (id_kandidat, sesi, nilai_kepuasan, tanggal) VALUES (?, ?, ?, NOW())";
    
    $stmt = $koneksi->prepare($sql);
    
    // Bind parameter dan eksekusi
    if ($stmt) {
        $stmt->bind_param("iii", $id_kandidat, $sesi, $nilai_kepuasan);
        
        if ($stmt->execute()) {
            // Jika berhasil, set status di session
            $_SESSION[$session_key] = true;
            $response['status'] = 'success';
            $response['message'] = 'Penilaian berhasil dikirim!';
        } else {
            $response['message'] = 'Gagal menyimpan penilaian: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Gagal menyiapkan statement: ' . $koneksi->error;
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

$koneksi->close();

echo json_encode($response);
?>