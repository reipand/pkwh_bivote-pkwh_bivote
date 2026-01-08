<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Input tidak valid.'];

if (isset($_POST['nik']) && isset($_POST['password'])) {
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    // Query menggunakan prepared statement untuk keamanan
    $stmt = $koneksi->prepare("SELECT id, nik, nama_lengkap, jabatan, status_memilih, id_kandidat_dipilih, password FROM guru WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (cek apakah hashed atau plain text untuk backward compatibility)
        $password_valid = false;
        if (password_verify($password, $user['password'])) {
            $password_valid = true;
        } elseif ($password === $user['password']) {
            // Fallback untuk password plain text (untuk akun lama)
            $password_valid = true;
        }
        
        if ($password_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nik'] = $user['nik'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];
            $_SESSION['user_jabatan'] = $user['jabatan'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_type'] = 'guru'; // Menandai bahwa ini adalah guru

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
            $response['message'] = 'NIK atau Password salah.';
        }
    } else {
        $response['message'] = 'NIK atau Password salah.';
    }
    $stmt->close();
}

echo json_encode($response);
$koneksi->close();
?>
