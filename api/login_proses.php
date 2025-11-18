<?php
// Pastikan metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Mulai session
    session_start();

    // Ambil data dari form dan bersihkan (sanitize)
    $nis = htmlspecialchars($_POST['nis']);
    $password = htmlspecialchars($_POST['password']);

    // Validasi sederhana: pastikan field tidak kosong
    if (empty($nis) || empty($password)) {
        // Redirect kembali ke halaman login dengan pesan error
        header("Location: ../page/login.php?error=fields_empty");
        exit();
    }

    // --- Proses Autentikasi (Contoh dengan data statis) ---
    // Dalam aplikasi nyata, Anda akan melakukan query ke database.
    $valid_users = [
        "12345" => "2005-01-20", // NIS: "12345", Password: "2005-01-20"
        "67890" => "2006-03-15"  // NIS: "67890", Password: "2006-03-15"
    ];
    
    // Periksa apakah NIS ada di array dan password cocok
    if (isset($valid_users[$nis]) && $valid_users[$nis] === $password) {
        // Autentikasi berhasil
        $_SESSION['loggedin'] = true;
        $_SESSION['nis'] = $nis;

        // Redirect ke halaman dashboard atau halaman utama
        header("Location: ../page/dashboard.php");
        exit();
    } else {
        // Autentikasi gagal
        // Redirect kembali ke halaman login dengan pesan error
        header("Location: ../page/login.php?error=invalid_credentials");
        exit();
    }

} else {
    // Jika diakses langsung tanpa POST, arahkan kembali ke halaman login
    header("Location: ../page/login.php");
    exit();
}
?>