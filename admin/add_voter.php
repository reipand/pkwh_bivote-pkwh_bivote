<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}

$message = '';
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
    $nis = htmlspecialchars(trim($_POST['nis']));
    $tanggal_lahir = htmlspecialchars(trim($_POST['tanggal_lahir']));
    $status_memilih = isset($_POST['status_memilih']) ? 1 : 0;

    if (empty($nama_lengkap) || empty($nis) || empty($tanggal_lahir)) {
        $message = "Semua field harus diisi.";
        $is_error = true;
    } else {
        $stmt = $koneksi->prepare("INSERT INTO pemilih (nama_lengkap, nis, tanggal_lahir, status_memilih, id_kandidat_dipilih) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nama_lengkap, $nis, $tanggal_lahir, $status_memilih, $id_kandidat_dipilih);

        if ($stmt->execute()) {
            $message = "Pemilih baru berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan pemilih: " . $stmt->error;
            $is_error = true;
        }
        $stmt->close();
    }
}
$koneksi->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pemilih - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Pemilih Baru</h1>
            <?php if ($message): ?>
                <div class="bg-<?php echo $is_error ? 'red' : 'green'; ?>-100 border border-<?php echo $is_error ? 'red' : 'green'; ?>-400 text-<?php echo $is_error ? 'red' : 'green'; ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <form action="add_voter.php" method="POST">
                    <div class="mb-4">
                        <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="nis" class="block text-gray-700 font-medium mb-2">NIS</label>
                        <input type="text" id="nis" name="nis" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="tanggal_lahir" class="block text-gray-700 font-medium mb-2">Tanggal Lahir (DD/MM/YY)</label>
                        <input type="text" id="tanggal_lahir" name="tanggal_lahir" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., 22/01/07" required>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="manage_votes.php" class="py-2 px-6 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors">Batal</a>
                        <button type="submit" class="py-2 px-6 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">Tambah Pemilih</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>