<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$message = '';
$is_error = false;

// Ambil status sesi dari database
$sql = "SELECT sesi_aktif FROM debat_sesi_status WHERE id = 1";
$result = $koneksi->query($sql);
$sesi_aktif = $result->fetch_assoc()['sesi_aktif'];

// Logika untuk mengubah sesi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'next_session' && $sesi_aktif < 3) {
        $next_sesi = $sesi_aktif + 1;
        $stmt = $koneksi->prepare("UPDATE debat_sesi_status SET sesi_aktif = ? WHERE id = 1");
        $stmt->bind_param("i", $next_sesi);
        if ($stmt->execute()) {
            $message = "Sesi berhasil diganti ke sesi $next_sesi.";
        } else {
            $message = "Gagal mengubah sesi.";
            $is_error = true;
        }
    } else if ($_POST['action'] === 'end_debat') {
        $stmt = $koneksi->prepare("UPDATE debat_sesi_status SET status = 'ended' WHERE id = 1");
        if ($stmt->execute()) {
            $message = "Debat telah diakhiri.";
        } else {
            $message = "Gagal mengakhiri debat.";
            $is_error = true;
        }
    }
    header("Location: manage_debat.php?message=" . urlencode($message) . "&is_error=" . $is_error);
    exit;
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $is_error = $_GET['is_error'] === '1';
}

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Debat - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Sesi Debat</h1>
            <?php if ($message): ?>
                <div class="bg-<?php echo $is_error ? 'red' : 'green'; ?>-100 border border-<?php echo $is_error ? 'red' : 'green'; ?>-400 text-<?php echo $is_error ? 'red' : 'green'; ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
            <div class="bg-white p-6 rounded-3xl shadow-lg w-full max-w-lg text-center">
                <p class="text-xl font-medium text-gray-700 mb-4">Sesi Debat saat ini:</p>
                <h2 class="text-5xl font-extrabold text-indigo-600 mb-8">Sesi <?php echo htmlspecialchars($sesi_aktif); ?></h2>
                <form action="manage_debat.php" method="POST" class="space-y-4">
                    <input type="hidden" name="sesi_aktif" value="<?php echo htmlspecialchars($sesi_aktif); ?>">
                    <?php if ($sesi_aktif < 3): ?>
                        <button type="submit" name="action" value="next_session" class="w-full bg-indigo-600 text-white py-3 rounded-full hover:bg-indigo-700 transition-colors">
                            Lanjut ke Sesi <?php echo $sesi_aktif + 1; ?>
                        </button>
                    <?php else: ?>
                        <button type="submit" name="action" value="end_debat" class="w-full bg-red-600 text-white py-3 rounded-full hover:bg-red-700 transition-colors">
                            Akhiri Debat
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </main>
    </div>
</body>
</html>