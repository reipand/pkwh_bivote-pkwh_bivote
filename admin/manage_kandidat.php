<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
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
                if (!empty($paths['foto_path']) && file_exists('../' . $paths['foto_path'])) {
                    @unlink('../' . $paths['foto_path']);
                }
                if (!empty($paths['video_path']) && file_exists('../' . $paths['video_path'])) {
                    @unlink('../' . $paths['video_path']);
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

// Logika untuk menghapus video saja
if (isset($_GET['action']) && $_GET['action'] == 'delete_video' && isset($_GET['id'])) {
    $id_to_delete_video = (int)$_GET['id'];

    // Ambil path video untuk dihapus dari server
    $stmt_video = $koneksi->prepare("SELECT video_path FROM kandidat WHERE id = ? LIMIT 1");
    $stmt_video->bind_param("i", $id_to_delete_video);
    $stmt_video->execute();
    $result_video = $stmt_video->get_result();
    $video_path = $result_video->fetch_assoc();
    $stmt_video->close();

    if (!$video_path) {
        $message = "Kandidat tidak ditemukan.";
    } else {
        // Update database untuk menghapus referensi video
        $stmt_update = $koneksi->prepare("UPDATE kandidat SET video_path = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $id_to_delete_video);
        
        if ($stmt_update->execute()) {
            // Hapus file video fisik jika ada
            if (!empty($video_path['video_path']) && file_exists('../' . $video_path['video_path'])) {
                if (@unlink('../' . $video_path['video_path'])) {
                    $message = "Video berhasil dihapus.";
                } else {
                    $message = "Referensi video dihapus dari database, tetapi file fisik gagal dihapus.";
                }
            } else {
                $message = "Referensi video berhasil dihapus dari database.";
            }
        } else {
            $message = "Gagal menghapus video dari database: " . $stmt_update->error;
        }
        $stmt_update->close();
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
                            <th class="py-2 px-4">Video</th>
                            <th class="py-2 px-4">Suara</th>
                            <th class="py-2 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($kandidat_list) > 0): ?>
                            <?php foreach ($kandidat_list as $kandidat): ?>
                                <tr class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4">
                                        <?php if (!empty($kandidat['foto_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-12 h-12 object-cover rounded-full">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                                <span class="text-gray-400 text-xs">No Foto</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['nis']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['kejar']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['usia']); ?></td>
                                    <td class="py-4 px-4 text-gray-600">
                                        <?php if (!empty($kandidat['video_path'])): ?>
                                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                ✓ Ada Video
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                ✗ No Video
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($kandidat['jumlah_suara']); ?></td>
                                    <td class="py-4 px-4 text-center space-x-2">
                                        <a href="edit_candidate.php?id=<?php echo $kandidat['id']; ?>" class="inline-block bg-blue-500 text-white py-1 px-3 rounded-full text-sm hover:bg-blue-600 transition-colors">
                                            Edit
                                        </a>
                                        <?php if (!empty($kandidat['video_path'])): ?>
                                            <button onclick="confirmDeleteVideo(<?php echo $kandidat['id']; ?>, '<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>')" class="bg-orange-500 text-white py-1 px-3 rounded-full text-sm hover:bg-orange-600 transition-colors">
                                                Hapus Video
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="confirmDelete(<?php echo $kandidat['id']; ?>, '<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>')" class="bg-red-500 text-white py-1 px-3 rounded-full text-sm hover:bg-red-600 transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-4 text-center text-gray-500">Belum ada kandidat yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Konfirmasi Hapus Kandidat -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center">
        <div class="relative p-8 border w-96 shadow-lg rounded-md bg-white text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Konfirmasi Hapus Kandidat</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus kandidat <span id="candidate-name" class="font-bold"></span>?</p>
            <p class="text-red-600 text-sm mb-4">⚠️ Tindakan ini akan menghapus semua data kandidat termasuk foto, video, dan suara!</p>
            <div class="flex justify-center space-x-4">
                <button id="cancel-delete-btn" class="py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                <a id="confirm-delete-link" href="#" class="py-2 px-4 bg-red-500 text-white rounded-md hover:bg-red-600">Hapus Kandidat</a>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Video -->
    <div id="delete-video-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center">
        <div class="relative p-8 border w-96 shadow-lg rounded-md bg-white text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Konfirmasi Hapus Video</h3>
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus video dari kandidat <span id="candidate-video-name" class="font-bold"></span>?</p>
            <p class="text-orange-600 text-sm mb-4">⚠️ Hanya video yang akan dihapus, data kandidat lainnya tetap tersimpan.</p>
            <div class="flex justify-center space-x-4">
                <button id="cancel-delete-video-btn" class="py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                <a id="confirm-delete-video-link" href="#" class="py-2 px-4 bg-orange-500 text-white rounded-md hover:bg-orange-600">Hapus Video</a>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk konfirmasi hapus kandidat
        function confirmDelete(id, nama) {
            const modal = document.getElementById('delete-modal');
            const candidateNameSpan = document.getElementById('candidate-name');
            const confirmLink = document.getElementById('confirm-delete-link');
            
            candidateNameSpan.textContent = nama;
            confirmLink.href = `?action=delete&id=${id}`;
            modal.classList.remove('hidden');
        }

        // Fungsi untuk konfirmasi hapus video saja
        function confirmDeleteVideo(id, nama) {
            const modal = document.getElementById('delete-video-modal');
            const candidateNameSpan = document.getElementById('candidate-video-name');
            const confirmLink = document.getElementById('confirm-delete-video-link');
            
            candidateNameSpan.textContent = nama;
            confirmLink.href = `?action=delete_video&id=${id}`;
            modal.classList.remove('hidden');
        }

        // Event listeners untuk modal hapus kandidat
        document.getElementById('cancel-delete-btn').addEventListener('click', function() {
            document.getElementById('delete-modal').classList.add('hidden');
        });

        // Event listeners untuk modal hapus video
        document.getElementById('cancel-delete-video-btn').addEventListener('click', function() {
            document.getElementById('delete-video-modal').classList.add('hidden');
        });

        // Tutup modal ketika klik di luar
        window.onclick = function(event) {
            const deleteModal = document.getElementById('delete-modal');
            const deleteVideoModal = document.getElementById('delete-video-modal');
            
            if (event.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
            if (event.target === deleteVideoModal) {
                deleteVideoModal.classList.add('hidden');
            }
        }

        // Auto-hide message setelah 5 detik
        <?php if (isset($message)): ?>
            setTimeout(function() {
                const alert = document.querySelector('.bg-green-100');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>