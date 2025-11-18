<?php
session_start();
require_once '../config/koneksi.php';

// Jika belum login, langsung ke login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Cek status memilih dari database
$has_voted = false;
if ($user_id > 0) {
    if ($stmt = $koneksi->prepare("SELECT status_memilih FROM pemilih WHERE id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $has_voted = ((int)$row['status_memilih']) === 1;
        }
        $stmt->close();
    }
}

// Jika belum voting, tampilkan kartu peringatan dan jangan logout
if (!$has_voted) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Peringatan - Belum Memilih</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-6 text-center">
            <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.518 11.59c.75 1.334-.213 2.986-1.742 2.986H3.48c-1.53 0-2.492-1.652-1.743-2.986L8.257 3.1zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800 mb-2">Anda belum memilih</h1>
            <p class="text-gray-600 mb-6">Silakan pilih kandidat jagoan Anda sebelum melakukan log out.</p>
            <a href="dashboard.php" class="inline-block px-6 py-2 rounded-full bg-indigo-600 text-white hover:bg-indigo-700">Kembali</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika sudah voting, lakukan proses logout seperti biasa
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>