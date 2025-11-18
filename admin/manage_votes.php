<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Logika untuk menangani penghapusan pemilih
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];

    // Gunakan transaksi: jika pemilih sudah memilih, kurangi jumlah suara kandidat terkait sebelum menghapus pemilih
    $koneksi->begin_transaction();
    try {
        $stmt_sel = $koneksi->prepare("SELECT status_memilih, id_kandidat_dipilih FROM pemilih WHERE id = ? LIMIT 1");
        $stmt_sel->bind_param("i", $id_to_delete);
        $stmt_sel->execute();
        $res = $stmt_sel->get_result();
        $row = $res->fetch_assoc();
        $stmt_sel->close();

        if ($row) {
            $status_memilih = (int)$row['status_memilih'];
            $id_kandidat = isset($row['id_kandidat_dipilih']) ? (int)$row['id_kandidat_dipilih'] : null;

            if ($status_memilih === 1 && $id_kandidat) {
                $stmt_dec = $koneksi->prepare("UPDATE kandidat SET jumlah_suara = GREATEST(jumlah_suara - 1, 0) WHERE id = ?");
                $stmt_dec->bind_param("i", $id_kandidat);
                $stmt_dec->execute();
                $stmt_dec->close();
            }

            $stmt_delete = $koneksi->prepare("DELETE FROM pemilih WHERE id = ?");
            $stmt_delete->bind_param("i", $id_to_delete);
            $stmt_delete->execute();
            $stmt_delete->close();

            $koneksi->commit();
            $message = "Pemilih berhasil dihapus.";
        } else {
            // jika tidak ditemukan, rollback dan beri pesan
            $koneksi->rollback();
            $message = "Pemilih tidak ditemukan.";
        }

    } catch (mysqli_sql_exception $ex) {
        $koneksi->rollback();
        $message = "Gagal menghapus pemilih: " . $ex->getMessage();
    }
}

// Ambil semua data pemilih dari database
$sql = "SELECT id, nama_lengkap, nis, status_memilih, id_kandidat_dipilih FROM pemilih ORDER BY id ASC";
$result = $koneksi->query($sql);

$voter_list = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $voter_list[] = $row;
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemilih - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Kelola Pemilih</h1>
                <a href="add_voter.php" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-full hover:bg-indigo-700 transition-colors">
                    + Tambah Pemilih
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
                            <th class="py-2 px-4">Nama Lengkap</th>
                            <th class="py-2 px-4">NIS</th>
                            <th class="py-2 px-4">Status Vote</th>
                            <th class="py-2 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($voter_list) > 0): ?>
                            <?php foreach ($voter_list as $voter): ?>
                                <tr class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($voter['nama_lengkap']); ?></td>
                                    <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($voter['nis']); ?></td>
                                    <td class="py-4 px-4 text-gray-600">
                                        <?php echo $voter['status_memilih'] ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah Vote</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Belum Vote</span>'; ?>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a href="edit_voter.php?id=<?php echo $voter['id']; ?>" class="inline-block bg-blue-500 text-white py-1 px-3 rounded-full text-sm hover:bg-blue-600 transition-colors">
                                            Edit
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $voter['id']; ?>, '<?php echo htmlspecialchars($voter['nama_lengkap']); ?>')" class="bg-red-500 text-white py-1 px-3 rounded-full text-sm hover:bg-red-600 transition-colors">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">Belum ada pemilih yang terdaftar.</td>
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
            <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus pemilih <span id="voter-name" class="font-bold"></span>?</p>
            <div class="flex justify-center space-x-4">
                <button id="cancel-delete-btn" class="py-2 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                <a id="confirm-delete-link" href="#" class="py-2 px-4 bg-red-500 text-white rounded-md hover:bg-red-600">Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, nama) {
            const modal = document.getElementById('delete-modal');
            const voterNameSpan = document.getElementById('voter-name');
            const confirmLink = document.getElementById('confirm-delete-link');
            
            voterNameSpan.textContent = nama;
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