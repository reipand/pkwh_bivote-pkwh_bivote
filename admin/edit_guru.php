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
    
    // Ambil data guru dari database (jangan ambil password mentah)
    $stmt = $koneksi->prepare("SELECT id, nama_lengkap, nik, jabatan, status_memilih, id_kandidat_dipilih FROM guru WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voter = $result->fetch_assoc();
    $stmt->close();

    // Jika pemilih tidak ditemukan, arahkan kembali ke halaman kelola pemilih
    if (!$voter) {
        header("Location: manage_guru.php");
        exit;
    }
} else {
    // Jika tidak ada ID di URL, arahkan kembali
    header("Location: manage_guru.php");
    exit;
}

// Tangani pengiriman formulir (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Jika admin memilih untuk menghapus keseluruhan akun guru
    if (isset($_POST['delete_guru'])) {
        $koneksi->begin_transaction();
        try {
            // Jika guru sudah memilih, kurangi jumlah suara kandidat terkait
            if ($voter['status_memilih'] == 1 && !is_null($voter['id_kandidat_dipilih'])) {
                $id_kandidat = (int)$voter['id_kandidat_dipilih'];
                $stmt_dec = $koneksi->prepare("UPDATE kandidat SET jumlah_suara = GREATEST(jumlah_suara - 1, 0) WHERE id = ?");
                $stmt_dec->bind_param("i", $id_kandidat);
                $stmt_dec->execute();
                $stmt_dec->close();
            }

            // Hapus guru dari tabel
            $stmt_del = $koneksi->prepare("DELETE FROM guru WHERE id = ?");
            $stmt_del->bind_param("i", $id);
            $stmt_del->execute();
            $stmt_del->close();

            $koneksi->commit();
            $_SESSION['success_message'] = "Guru berhasil dihapus.";
            header("Location: manage_guru.php");
            exit;

        } catch (mysqli_sql_exception $ex) {
            $koneksi->rollback();
            $message = "Gagal menghapus guru: " . $ex->getMessage();
            $is_error = true;
        }

    // Jika admin memilih untuk menghapus hanya suara guru
    } elseif (isset($_POST['delete_vote'])) {

        $id_kandidat_dihapus = $voter['id_kandidat_dipilih'];

        $koneksi->begin_transaction();
        try {
            if (!is_null($id_kandidat_dihapus) && $voter['status_memilih'] == 1) {
                $stmt_decrement = $koneksi->prepare(
                    "UPDATE kandidat SET jumlah_suara = GREATEST(jumlah_suara - 1, 0) WHERE id = ?"
                );
                $stmt_decrement->bind_param("i", $id_kandidat_dihapus);
                $stmt_decrement->execute();
                $stmt_decrement->close();
            }

            $stmt_reset = $koneksi->prepare("UPDATE guru SET id_kandidat_dipilih = NULL, status_memilih = 0 WHERE id = ?");
            $stmt_reset->bind_param("i", $id);
            $stmt_reset->execute();
            $stmt_reset->close();

            $koneksi->commit();
            $message = "Suara guru berhasil dihapus dan jumlah suara kandidat telah diperbarui.";
            $voter['status_memilih'] = 0;
            $voter['id_kandidat_dipilih'] = null;

        } catch (mysqli_sql_exception $exception) {
            $koneksi->rollback();
            $message = "Gagal menghapus suara: " . $exception->getMessage();
            $is_error = true;
        }

    } else {
        // Logika untuk memperbarui detail guru
        $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
        $nik = htmlspecialchars(trim($_POST['nik']));
        $jabatan = htmlspecialchars(trim($_POST['jabatan']));
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Cek jika field wajib tidak kosong
        if (empty($nama_lengkap) || empty($nik) || empty($jabatan)) {
            $message = "Nama, NIK, dan Jabatan harus diisi.";
            $is_error = true;
        } else {
                // Siapkan query update (jika password diisi, hash lalu update juga)
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_update = $koneksi->prepare("UPDATE guru SET nama_lengkap = ?, nik = ?, jabatan = ?, password = ? WHERE id = ?");
                    $stmt_update->bind_param("ssssi", $nama_lengkap, $nik, $jabatan, $hashed, $id);
                } else {
                    $stmt_update = $koneksi->prepare("UPDATE guru SET nama_lengkap = ?, nik = ?, jabatan = ? WHERE id = ?");
                    $stmt_update->bind_param("sssi", $nama_lengkap, $nik, $jabatan, $id);
                }

            if ($stmt_update->execute()) {
                $message = "Data guru berhasil diperbarui.";
                // Perbarui variabel data guru agar formulir menampilkan data terbaru
                $voter['nama_lengkap'] = $nama_lengkap;
                $voter['nik'] = $nik;
                $voter['jabatan'] = $jabatan;
            } else {
                $message = "Gagal memperbarui guru: " . $stmt_update->error;
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
    <title>Edit Guru - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script>
            // Konfirmasi untuk menghapus suara (guru)
            function confirmVoteDeletion() {
                return confirm("Apakah Anda yakin ingin menghapus suara guru ini? Jumlah suara kandidat terkait akan dikurangi satu. Tindakan ini tidak dapat diurungkan.");
            }

            // Konfirmasi untuk menghapus akun guru
            function confirmDeleteGuruAccount() {
                return confirm("Apakah Anda yakin ingin menghapus akun guru ini? Ini akan menghapus akun dan (jika sudah memilih) mengurangi jumlah suara kandidat terkait. Tindakan ini tidak dapat dikembalikan.");
            }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Guru: <?php echo htmlspecialchars($voter['nama_lengkap']); ?></h1>

            <?php if ($message): ?>
                <div class="bg-<?php echo $is_error ? 'red' : 'green'; ?>-100 border border-<?php echo $is_error ? 'red' : 'green'; ?>-400 text-<?php echo $is_error ? 'red' : 'green'; ?>-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <form action="edit_guru.php?id=<?php echo $voter['id']; ?>" method="POST">
                    <div class="mb-4">
                        <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($voter['nama_lengkap']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="nik" class="block text-gray-700 font-medium mb-2">NIK</label>
                        <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($voter['nik']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="jabatan" class="block text-gray-700 font-medium mb-2">Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($voter['jabatan']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password (kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" id="password" name="password" value="" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="mb-6 flex items-center">
                        <input type="checkbox" id="status_memilih" name="status_memilih" value="1" disabled <?php echo $voter['status_memilih'] ? 'checked' : ''; ?> class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-not-allowed">
                        <label for="status_memilih" class="ml-2 block text-sm text-gray-900">Sudah memilih</label>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="space-x-2">
                            <?php if ($voter['status_memilih']): ?>
                                <button type="submit" name="delete_vote" value="1" onclick="return confirmVoteDeletion();" class="py-2 px-5 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors">
                                    Hapus Suara
                                </button>
                            <?php endif; ?>

                            <!-- Tombol hapus akun guru -->
                            <button type="submit" name="delete_guru" value="1" onclick="return confirmDeleteGuruAccount();" class="py-2 px-5 bg-red-800 text-white rounded-full hover:bg-red-900 transition-colors">
                                Hapus Guru
                            </button>
                        </div>

                        <div class="flex space-x-4">
                            <a href="manage_guru.php" class="py-2 px-6 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors">Batal</a>
                            <button type="submit" class="py-2 px-6 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">Perbarui Guru</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
