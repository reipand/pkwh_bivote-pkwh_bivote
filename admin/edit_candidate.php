<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$message = '';
$is_error = false;

// Ambil ID kandidat dari URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data kandidat dari database
    $stmt = $koneksi->prepare("SELECT * FROM kandidat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kandidat = $result->fetch_assoc();
    $stmt->close();
    
    // Jika kandidat tidak ditemukan, alihkan
    if (!$kandidat) {
        header("Location: manage_candidates.php");
        exit;
    }
} else {
    header("Location: manage_candidates.php");
    exit;
}

// Logika untuk menangani pembaruan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
    $nis = htmlspecialchars(trim($_POST['nis']));
    $visi = htmlspecialchars(trim($_POST['visi']));
    $misi = htmlspecialchars(trim($_POST['misi']));
    $kejar = htmlspecialchars(trim($_POST['kejar']));
    $usia = (int)$_POST['usia'];
    
    $foto_path = $kandidat['foto_path'];
    $video_path = $kandidat['video_path'];
    
    // Proses unggah foto baru jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/image/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $foto_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_foto_name = 'kandidat_' . $id . '_' . time() . '.' . $foto_extension;
        $foto_upload_path = $upload_dir . $new_foto_name;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_upload_path)) {
            // Hapus foto lama jika ada
            if ($kandidat['foto_path'] && file_exists($kandidat['foto_path'])) {
                unlink($kandidat['foto_path']);
            }
            $foto_path = $foto_upload_path;
        } else {
            $message = 'Gagal mengunggah foto.';
            $is_error = true;
        }
    }
    
    // Proses unggah video baru jika ada
    if (!$is_error && isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        // Logika kompresi video menggunakan FFMPEG
        // ... (Kode ini harus memanggil atau mengimplementasikan logika kompresi seperti di `upload_video.php`)
        // Untuk contoh ini, kita asumsikan video sudah berhasil diunggah dan dikompresi
        
        $upload_dir_video = '../assets/videos/';
        if (!is_dir($upload_dir_video)) {
            mkdir($upload_dir_video, 0777, true);
        }
        $video_extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $new_video_name = 'video_' . $id . '_' . time() . '.' . $video_extension;
        $video_upload_path = $upload_dir_video . $new_video_name;

        // Contoh sederhana tanpa kompresi FFMPEG (HANYA UNTUK DEMO)
        if (move_uploaded_file($_FILES['video']['tmp_name'], $video_upload_path)) {
            // Hapus video lama jika ada
            if ($kandidat['video_path'] && file_exists($kandidat['video_path'])) {
                unlink($kandidat['video_path']);
            }
            $video_path = $video_upload_path;
        } else {
            $message = 'Gagal mengunggah video.';
            $is_error = true;
        }
    }
    
    if (!$is_error) {
        // Perbarui data di database
        $stmt = $koneksi->prepare("UPDATE kandidat SET nama_lengkap = ?, nis = ?, visi = ?, misi = ?, kejar = ?, usia = ?, foto_path = ?, video_path = ? WHERE id = ?");
        $stmt->bind_param("sssssissi", $nama_lengkap, $nis, $visi, $misi, $kejar, $usia, $foto_path, $video_path, $id);
        
        if ($stmt->execute()) {
            $message = "Data kandidat berhasil diperbarui.";
            // Refresh data setelah update agar formulir terisi dengan data terbaru
            $stmt_refresh = $koneksi->prepare("SELECT * FROM kandidat WHERE id = ?");
            $stmt_refresh->bind_param("i", $id);
            $stmt_refresh->execute();
            $result_refresh = $stmt_refresh->get_result();
            $kandidat = $result_refresh->fetch_assoc();
            $stmt_refresh->close();
        } else {
            $message = "Gagal memperbarui data: " . $stmt->error;
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
    <title>Edit Kandidat - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Kandidat: <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></h1>

            <?php if ($message): ?>
                <div class="bg-<?php echo $is_error ? 'red' : 'green'; ?>-100 border border-<?php echo $is_error ? 'red' : 'green'; ?>-400 text-<?php echo $is_error ? 'red' : 'green'; ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <form action="edit_candidate.php?id=<?php echo $kandidat['id']; ?>" method="POST" enctype="multipart/form-data">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="nis" class="block text-gray-700 font-medium mb-2">NIS</label>
                            <input type="text" id="nis" name="nis" value="<?php echo htmlspecialchars($kandidat['nis']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="kejar" class="block text-gray-700 font-medium mb-2">Kelas/Jurusan</label>
                            <input type="text" id="kejar" name="kejar" value="<?php echo htmlspecialchars($kandidat['kejar']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="usia" class="block text-gray-700 font-medium mb-2">Usia</label>
                            <input type="number" id="usia" name="usia" value="<?php echo htmlspecialchars($kandidat['usia']); ?>" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="visi" class="block text-gray-700 font-medium mb-2">Visi</label>
                        <textarea id="visi" name="visi" rows="3" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required><?php echo htmlspecialchars($kandidat['visi']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="misi" class="block text-gray-700 font-medium mb-2">Misi (Pisahkan dengan titik koma ';')</label>
                        <textarea id="misi" name="misi" rows="5" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required><?php echo htmlspecialchars($kandidat['misi']); ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="foto" class="block text-gray-700 font-medium mb-2">Foto Kandidat</label>
                            <?php if ($kandidat['foto_path']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto Sekarang" class="w-24 h-24 object-cover rounded-full">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="foto" name="foto" accept="image/*" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto.</p>
                        </div>
                        <div>
                            <label for="video" class="block text-gray-700 font-medium mb-2">Video Visi Misi</label>
                            <?php if ($kandidat['video_path']): ?>
                                <div class="mb-2">
                                    <video src="<?php echo htmlspecialchars($kandidat['video_path']); ?>" controls class="w-full rounded-lg"></video>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="video" name="video" accept="video/*" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah video.</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="manage_candidates.php" class="py-2 px-6 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors">Batal</a>
                        <button type="submit" class="py-2 px-6 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition-colors">Perbarui Data</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>