<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Input tidak valid.'];

// Debug logging
error_log("Login attempt - NIS: " . (isset($_POST['nis']) ? $_POST['nis'] : 'not set') . ", Password: " . (isset($_POST['password']) ? $_POST['password'] : 'not set'));

if (isset($_POST['nis']) && isset($_POST['password'])) {
    $nis = trim((string)$_POST['nis']);
    $passwordInput = trim((string)$_POST['password']); // tanggal lahir sebagai password

    // Normalisasi password: terima "DD/MM/YY" atau "DDMMYY"
    $passwordDigitsOnly = str_replace('/', '', $passwordInput);
    $passwordWithSlashes = $passwordInput;

    if (preg_match('/^\d{6}$/', $passwordDigitsOnly)) {
        // Ubah menjadi DD/MM/YY
        $passwordWithSlashes = substr($passwordDigitsOnly, 0, 2) . '/' . substr($passwordDigitsOnly, 2, 2) . '/' . substr($passwordDigitsOnly, 4, 2);
    } elseif (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $passwordInput)) {
        // Sudah berformat DD/MM/YY
        $passwordWithSlashes = $passwordInput;
    } else {
        $response['message'] = 'Format tanggal lahir tidak valid. Gunakan DD/MM/YY atau DDMMYY.';
        echo json_encode($response);
        exit;
    }

    // Query menggunakan prepared statement untuk keamanan
    // Terima kecocokan baik yang tersimpan dengan "/" maupun tanpa "/"
   $stmt = $koneksi->prepare("SELECT id, nis, nama_lengkap, status_memilih, id_kandidat_dipilih FROM pemilih WHERE nis = ? AND (TRIM(tanggal_lahir) = ? OR TRIM(tanggal_lahir) = ?)");
    
    // --- DEBUG LEVEL 1: Cek apakah prepare berhasil ---
    if ($koneksi->error) {
        error_log("SQL Prepare Error: " . $koneksi->error);
    }
    
    // Perhatikan: Kita bind 3 parameter: NIS, Tanggal dengan slash, dan Tanggal TANPA slash.
    $stmt->bind_param("sss", $nis, $passwordWithSlashes, $passwordDigitsOnly);
    
    // --- DEBUG LEVEL 2: Cek apakah bind_param berhasil ---
    if ($stmt->error) {
        error_log("SQL Bind Param Error: " . $stmt->error);
    }
    
    $stmt->execute();
    
    // --- DEBUG LEVEL 3: Cek apakah execute berhasil ---
    if ($stmt->error) {
        error_log("SQL Execute Error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['id']; // Menambahkan ID pengguna ke sesi
        $_SESSION['user_nis'] = $user['nis'];
        $_SESSION['user_nama'] = $user['nama_lengkap'];
        $_SESSION['is_logged_in'] = true;

        if ($user['status_memilih'] == 1) {
            // Jika sudah memilih, alihkan ke halaman konfirmasi
            $_SESSION['vote_status'] = 'already_voted';
            $response = [
                'status' => 'success',
                'message' => 'Login berhasil!',
                'redirect' => 'vote_confirmation.php' 
            ];
        } else {
            // Jika belum memilih, alihkan ke dashboard
            $response = [
                'status' => 'success',
                'message' => 'Login berhasil!',
                'redirect' => 'dashboard.php' 
            ];
        }
    } else {
        $response['message'] = 'NIS atau Tanggal Lahir salah.';
    }
    $stmt->close();
}

echo json_encode($response);

$koneksi->close();
?>