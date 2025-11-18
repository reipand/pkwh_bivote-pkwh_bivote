<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Input tidak valid.'];

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT username, password FROM admin WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['is_admin_logged_in'] = true;
                $response = ['status' => 'success', 'message' => 'Login admin berhasil!'];
            } else {
                $response['message'] = 'Username atau Password salah.';
            }
        } else {
            $response['message'] = 'Username atau Password salah.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Terjadi kesalahan pada database.';
    }
}

$koneksi->close();
echo json_encode($response);
exit;
?>
