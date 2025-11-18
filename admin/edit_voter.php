<?php
session_start();

require_once '../config/koneksi.php';


// Pastikan hanya admin yang sudah login bisa akses.
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}


$message = '';
$is_error = false;
$voter = null;

// Pastikan ID pemilih ada di URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Ambil ID dari URL dan pastikan itu integer
    
    // Ambil data pemilih dari database
    $stmt = $koneksi->prepare("SELECT id, nama_lengkap, nis, tanggal_lahir, status_memilih, id_kandidat_dipilih FROM pemilih WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voter = $result->fetch_assoc();
    $stmt->close();

    // Jika pemilih tidak ditemukan, arahkan kembali ke halaman kelola pemilih
    if (!$voter) {
        header("Location: manage_votes.php");
        exit;
    }
} else {
    // Jika tidak ada ID di URL, arahkan kembali
    header("Location: manage_votes.php");
    exit;
}

// Tangani pengiriman formulir (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Logika untuk menghapus suara
    if (isset($_POST['delete_vote'])) {
        
        $id_kandidat_dihapus = $voter['id_kandidat_dipilih'];

        $koneksi->begin_transaction();

        try {

            if (!is_null($id_kandidat_dihapus) && $voter['status_memilih'] == 1) {
                $stmt_decrement = $koneksi->prepare(
                    "UPDATE kandidat SET jumlah_suara = jumlah_suara - 1 WHERE id = ? AND jumlah_suara > 0"
                );
                $stmt_decrement->bind_param("i", $id_kandidat_dihapus);
                $stmt_decrement->execute();
                $stmt_decrement->close();
            }

            $stmt_reset = $koneksi->prepare("UPDATE pemilih SET id_kandidat_dipilih = NULL, status_memilih = 0 WHERE id = ?");
            $stmt_reset->bind_param("i", $id);
            $stmt_reset->execute();
            $stmt_reset->close();

            $koneksi->commit();
            $message = "Suara pemilih berhasil dihapus dan jumlah suara kandidat telah diperbarui.";
            $voter['status_memilih'] = 0;
            $voter['id_kandidat_dipilih'] = null;

        } catch (mysqli_sql_exception $exception) {
 
            $koneksi->rollback();
            $message = "Gagal menghapus suara: " . $exception->getMessage();
            $is_error = true;
        }


    } else {
        // Logika untuk memperbarui detail pemilih
        $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
        $nis = htmlspecialchars(trim($_POST['nis']));
        $tanggal_lahir = htmlspecialchars(trim($_POST['tanggal_lahir']));
        
        // Cek jika field wajib tidak kosong
        if (empty($nama_lengkap) || empty($nis) || empty($tanggal_lahir)) {
            $message = "Semua field harus diisi.";
            $is_error = true;
        } else {

            $stmt_update = $koneksi->prepare("UPDATE pemilih SET nama_lengkap = ?, nis = ?, tanggal_lahir = ? WHERE id = ?");
            $stmt_update->bind_param("sssi", $nama_lengkap, $nis, $tanggal_lahir, $id);

            if ($stmt_update->execute()) {
                $message = "Data pemilih berhasil diperbarui.";
                // Perbarui variabel data pemilih agar formulir menampilkan data terbaru
                $voter['nama_lengkap'] = $nama_lengkap;
                $voter['nis'] = $nis;
                $voter['tanggal_lahir'] = $tanggal_lahir;
            } else {
                $message = "Gagal memperbarui pemilih: " . $stmt_update->error;
                $is_error = true;
            }
            $stmt_update->close();
        }
    }
}
$koneksi->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemilih - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script>
        // Gunakan modal kustom daripada alert()
        function confirmVoteDeletion() {
            return confirm("Apakah Anda yakin ingin menghapus suara pemilih ini? Jumlah suara kandidat terkait akan dikurangi satu. Tindakan ini tidak dapat diurungkan.");
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Pemilih: <?php echo htmlspecialchars($voter['nama_lengkap']); ?></h1>

            <?php if ($message): ?>
                <div class="bg-<?php echo $is_error ? 'red' : 'green'; ?>-100 border border-<?php echo $is_error ? 'red' : 'green'; ?>-400 text-<?php echo $is_error ? 'red' : 'green'; ?>-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <form action="edit_voter.php?id=<?php echo $voter['id']; ?>" method="POST">
                    <div class="mb-4">
                        <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($voter['nama_lengkap']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="nis" class="block text-gray-700 font-medium mb-2">NIS</label>
                        <input type="text" id="nis" name="nis" value="<?php echo htmlspecialchars($voter['nis']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="tanggal_lahir" class="block text-gray-700 font-medium mb-2">Tanggal Lahir (DD/MM/YY)</label>
                        <input type="text" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($voter['tanggal_lahir']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., 22/01/07" required>
                    </div>
                    <div class="mb-6 flex items-center">
                        <input type="checkbox" id="status_memilih" name="status_memilih" value="1" disabled <?php echo $voter['status_memilih'] ? 'checked' : ''; ?> class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-not-allowed">
                        <label for="status_memilih" class="ml-2 block text-sm text-gray-900">Sudah memilih</label>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <?php if ($voter['status_memilih']): ?>
                                <button type="submit" name="delete_vote" value="1" onclick="return confirmVoteDeletion();" class="py-2 px-5 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors">
                                    Hapus Suara
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="flex space-x-4">
                            <a href="manage_votes.php" class="py-2 px-6 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors">Batal</a>
                            <button type="submit" class="py-2 px-6 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">Perbarui Pemilih</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
