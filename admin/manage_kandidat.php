<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Logika untuk menangani penghapusan kandidat
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];

    // Ambil path foto dan video untuk dihapus dari server serta pastikan kandidat ada
    $stmt_path = $koneksi->prepare("SELECT foto_path, video_path FROM kandidat WHERE id = ? LIMIT 1");
    $stmt_path->bind_param("i", $id_to_delete);
    $stmt_path->execute();
    $result_path = $stmt_path->get_result();
    $paths = $result_path->fetch_assoc();
    $stmt_path->close();

    if (!$paths) {
        $message = "Kandidat tidak ditemukan.";
    } else {
        // Gunakan transaksi: reset pemilih yang memilih kandidat ini, lalu hapus kandidat
        $koneksi->begin_transaction();
        try {
            // Set ulang pemilih yang memilih kandidat ini
            $stmt_reset = $koneksi->prepare("UPDATE pemilih SET id_kandidat_dipilih = NULL, status_memilih = 0 WHERE id_kandidat_dipilih = ?");
            $stmt_reset->bind_param("i", $id_to_delete);
            $stmt_reset->execute();
            $stmt_reset->close();

            // Hapus kandidat
            $stmt_delete = $koneksi->prepare("DELETE FROM kandidat WHERE id = ?");
            $stmt_delete->bind_param("i", $id_to_delete);
            $stmt_delete->execute();
            $affected = $stmt_delete->affected_rows;
            $stmt_delete->close();

            if ($affected > 0) {
                $koneksi->commit();
                // Setelah commit berhasil, hapus file fisik jika ada
                if (!empty($paths['foto_path']) && file_exists($paths['foto_path'])) {
                    @unlink($paths['foto_path']);
                }
                if (!empty($paths['video_path']) && file_exists($paths['video_path'])) {
                    @unlink($paths['video_path']);
                }
                $message = "Kandidat berhasil dihapus dan suara terkait telah di-reset.";
            } else {
                $koneksi->rollback();
                $message = "Kandidat tidak ditemukan atau gagal dihapus.";
            }

        } catch (mysqli_sql_exception $ex) {
            $koneksi->rollback();
            $message = "Gagal menghapus kandidat: " . $ex->getMessage();
        }
    }
}

// Ambil semua data kandidat dari database
$sql = "SELECT id, nama_lengkap, nis, video_path, foto_path, kejar, usia, jumlah_suara FROM kandidat ORDER BY id ASC";
$result = $koneksi->query($sql);

$kandidat_list = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $kandidat_list[] = $row;
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Kelola Kandidat</h1>
                <a href="add_kandidat.php" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-full hover:bg-indigo-700 transition-colors">
                    + Tambah Kandidat
                </a>
            </div>

            <?php if (isset($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-gray-600 border-b">
                            <th class="py-2 px-4">Foto</th>
                            <th class="py-2 px-4">Nama</th>
                            <th class="py-2 px-4">NIS</th>
                            <th class="py-2 px-4">Kelas/Jurusan</th>
                            <th class="py-2 px-4">Usia</th>
                            <th class="py-2 px-4">Suara</th>
                            <th class="py-2 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($kandidat_list) > 0): ?>
                            <?php foreach ($kandidat_list as $kandidat): ?>
                                <tr class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4">
                                        <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-12 h-12 object-cover rounded-full">
                                    </td>
                                    <td class="py-4 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['nis']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['kejar']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['usia']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['jumlah_suara']); ?></td>
                                    <td class="py-4 px-4 text-center">
                                        <a href="edit_candidate.php?id=<?php echo $kandidat['id']; ?>" class="inline-block bg-blue-500 text-white py-1 px-3 rounded-full text-sm hover:bg-blue-600 transition-colors">
                                            Edit
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $kandidat['id']; ?>, '<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>')" class="bg-red-500 text-white py-1 px-3 rounded-full text-sm hover:bg-red-600 transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-500">Belum ada kandidat yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center">
        <div class="relative p-8 border w-96 shadow-lg rounded-md bg-white text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus kandidat <span id="candidate-name" class="font-bold"></span>?</p>
            <div class="flex justify-center space-x-4">
                <button id="cancel-delete-btn" class="py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                <a id="confirm-delete-link" href="#" class="py-2 px-4 bg-red-500 text-white rounded-md hover:bg-red-600">Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, nama) {
            const modal = document.getElementById('delete-modal');
            const candidateNameSpan = document.getElementById('candidate-name');
            const confirmLink = document.getElementById('confirm-delete-link');
            
            candidateNameSpan.textContent = nama;
            confirmLink.href = `?action=delete&id=${id}`;
            modal.classList.remove('hidden');
        }

        document.getElementById('cancel-delete-btn').addEventListener('click', function() {
            document.getElementById('delete-modal').classList.add('hidden');
        });

        window.onclick = function(event) {
            const modal = document.getElementById('delete-modal');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>