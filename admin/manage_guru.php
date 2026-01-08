<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}
// Logika penghapusan guru (langsung dari halaman kelola)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];

    $koneksi->begin_transaction();
    try {
        // Ambil informasi guru (termasuk status memilih dan kandidat yang dipilih)
        $stmt = $koneksi->prepare("SELECT status_memilih, id_kandidat_dipilih FROM guru WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $res = $stmt->get_result();
        $guru_row = $res->fetch_assoc();
        $stmt->close();

        if (!$guru_row) {
            $koneksi->rollback();
            $_SESSION['error_message'] = "Guru tidak ditemukan.";
            header('Location: manage_guru.php');
            exit;
        }

        if ((int)$guru_row['status_memilih'] === 1 && !is_null($guru_row['id_kandidat_dipilih'])) {
            $id_kandidat = (int)$guru_row['id_kandidat_dipilih'];
            $stmt_dec = $koneksi->prepare("UPDATE kandidat SET jumlah_suara = GREATEST(jumlah_suara - 1, 0) WHERE id = ?");
            $stmt_dec->bind_param("i", $id_kandidat);
            $stmt_dec->execute();
            $stmt_dec->close();
        }

        $stmt_del = $koneksi->prepare("DELETE FROM guru WHERE id = ?");
        $stmt_del->bind_param("i", $id_to_delete);
        $stmt_del->execute();
        $affected = $stmt_del->affected_rows;
        $stmt_del->close();

        if ($affected > 0) {
            $koneksi->commit();
            $_SESSION['success_message'] = "Guru berhasil dihapus.";
            header('Location: manage_guru.php');
            exit;
        } else {
            $koneksi->rollback();
            $_SESSION['error_message'] = "Gagal menghapus guru.";
            header('Location: manage_guru.php');
            exit;
        }

    } catch (mysqli_sql_exception $ex) {
        $koneksi->rollback();
        $_SESSION['error_message'] = "Gagal menghapus guru: " . $ex->getMessage();
        header('Location: manage_guru.php');
        exit;
    }
}

// Ambil data guru
$sql = "SELECT id, nama_lengkap, nik, jabatan, status_memilih, created_at FROM guru ORDER BY nama_lengkap ASC";
$result = $koneksi->query($sql);

$guru_list = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Normalisasi tipe data
        $row['id'] = isset($row['id']) ? (int)$row['id'] : null;
        $row['status_memilih'] = isset($row['status_memilih']) && (int)$row['status_memilih'] === 1 ? 1 : 0;
        $guru_list[] = $row;
    }
    $result->free();
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Kelola Guru</h1>
                    <p class="text-gray-600 mt-1">Kelola data guru yang dapat melakukan voting.</p>
                </div>
                <button onclick="openAddGuruModal()" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-full hover:bg-indigo-700 transition-colors">
                    Tambah Guru
                </button>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Guru</h2>
                <div class="table-container">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">No</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Nama Lengkap</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">NIK</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Jabatan</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status Memilih</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tanggal Daftar</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guru_list as $index => $guru): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-600"><?php echo $index + 1; ?></td>
                                    <td class="py-3 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($guru['nik']); ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($guru['jabatan']); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $guru['status_memilih'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $guru['status_memilih'] ? 'Sudah Memilih' : 'Belum Memilih'; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600"><?php echo date('d/m/Y', strtotime($guru['created_at'])); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <button onclick="editGuru(<?php echo $guru['id']; ?>)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
                                            <button onclick="deleteGuru(<?php echo $guru['id']; ?>)" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Guru -->
    <div id="addGuruModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
        <div class="relative p-8 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-xl bg-white">
            <h3 class="text-2xl font-bold mb-4">Tambah Guru</h3>
            <form id="addGuruForm" action="add_guru.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                        <input type="text" id="nik" name="nik" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <input type="text" id="jabatan" name="jabatan" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeAddGuruModal()" class="py-2 px-6 bg-gray-500 text-white rounded-full hover:bg-gray-600 transition-colors">Batal</button>
                    <button type="submit" class="py-2 px-6 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddGuruModal() {
            document.getElementById('addGuruModal').classList.remove('hidden');
        }

        function closeAddGuruModal() {
            document.getElementById('addGuruModal').classList.add('hidden');
        }

        function editGuru(id) {
            // Arahkan ke halaman edit guru
            window.location.href = 'edit_guru.php?id=' + encodeURIComponent(id);
        }

        function deleteGuru(id) {
            if (confirm('Apakah Anda yakin ingin menghapus guru ini? Tindakan ini akan menghapus akun guru dan mengurangi suara kandidat terkait jika sudah memilih.')) {
                // Arahkan ke endpoint delete pada halaman ini
                window.location.href = 'manage_guru.php?action=delete&id=' + encodeURIComponent(id);
            }
        }
