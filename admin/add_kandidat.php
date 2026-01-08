<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang sudah login bisa akses
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}
$message = "";

// Logika untuk menangani pengiriman formulir
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $nama_lengkap = $_POST['nama_lengkap'];
    $nis = $_POST['nis'];
    $kejar = $_POST['kejar'];
    $usia = $_POST['usia'];
    $visi = $_POST['visi'];
    $misi = $_POST['misi'];
    $program_kerja = $_POST['program_kerja'];
    
    // Direktori untuk menyimpan file yang diunggah
    $foto_upload_dir = '../assets/image/';
    $video_upload_dir = '../assets/videos/';
    $foto_path = null;
    $video_path = null;

    // Pastikan direktori ada
    if (!is_dir($foto_upload_dir)) {
        mkdir($foto_upload_dir, 0755, true);
    }
    if (!is_dir($video_upload_dir)) {
        mkdir($video_upload_dir, 0755, true);
    }

    // --- Proses Upload Foto ---
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = $_FILES['foto'];
        
        // Validasi tipe file foto
        $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $foto['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_image_types)) {
            $message = 'Format foto tidak didukung. Gunakan JPG, PNG, atau GIF.';
        } elseif ($foto['size'] > 5 * 1024 * 1024) {
            $message = 'Ukuran foto terlalu besar. Maksimal 5MB.';
        } else {
            $foto_ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_filename = 'kandidat_' . time() . '_' . uniqid() . '.' . $foto_ext;
            $foto_destination = $foto_upload_dir . $foto_filename;

            // Pindahkan file yang diunggah ke direktori tujuan
            if (move_uploaded_file($foto['tmp_name'], $foto_destination)) {
                $foto_path = 'assets/image/' . $foto_filename;
            } else {
                $message = "Gagal mengunggah foto.";
            }
        }
    }

    // --- Proses Upload Video ---
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video = $_FILES['video'];
        
        // Validasi tipe file video
        $allowed_video_types = ['video/mp4', 'video/webm', 'video/quicktime', 'video/mov', 'video/avi', 'video/x-msvideo'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['video']['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_video_types)) {
            $message = 'Format video tidak didukung. Gunakan MP4, WebM, MOV, atau AVI. (MIME type terdeteksi: ' . $mime_type . ')';
        } elseif ($video['size'] > 50 * 1024 * 1024) {
            $message = 'Ukuran video terlalu besar. Maksimal 50MB.';
        } else {
            $video_ext = pathinfo($video['name'], PATHINFO_EXTENSION);
            $video_filename = 'video_' . time() . '_' . uniqid() . '.' . $video_ext;
            $video_destination = $video_upload_dir . $video_filename;

            // Pindahkan file yang diunggah ke direktori tujuan
            if (move_uploaded_file($video['tmp_name'], $video_destination)) {
                $video_path = 'assets/videos/' . $video_filename;
            } else {
                $message = "Gagal mengunggah video.";
            }
        }
    }

    // Jika tidak ada kesalahan upload, simpan data ke database
    if (empty($message)) {
        // Query untuk menyimpan data
        $sql = "INSERT INTO kandidat (nama_lengkap, nis, kejar, usia, visi, misi, program_kerja, foto_path, video_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $koneksi->prepare($sql);
        // Bind parameter ke statement
        $stmt->bind_param("sisssssss", $nama_lengkap, $nis, $kejar, $usia, $visi, $misi, $program_kerja, $foto_path, $video_path);
        
        if ($stmt->execute()) {
            $message = "Kandidat **" . htmlspecialchars($nama_lengkap) . "** berhasil ditambahkan!";
        } else {
            $message = "Terjadi kesalahan saat menambahkan kandidat: " . $stmt->error;
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
    <title>Tambah Kandidat - Admin BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Kandidat Baru</h1>
            
            <?php if (!empty($message)): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-3xl shadow-lg">
                <form action="add_kandidat.php" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label for="nis" class="block text-gray-700 font-medium mb-2">NIS</label>
                                <input type="text" id="nis" name="nis" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label for="kejar" class="block text-gray-700 font-medium mb-2">Kelas/Jurusan</label>
                                <input type="text" id="kejar" name="kejar" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label for="usia" class="block text-gray-700 font-medium mb-2">Usia</label>
                                <input type="number" id="usia" name="usia" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label for="foto" class="block text-gray-700 font-medium mb-2">Foto Kandidat</label>
                                <input type="file" id="foto" name="foto" accept="image/*" required class="w-full text-gray-700">
                                <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG, GIF. Max ukuran 5MB.</p>
                            </div>
                            <div class="mb-4">
                                <label for="video" class="block text-gray-700 font-medium mb-2">Video Kampanye (Visi Misi & Program Kerja)</label>
                                <input type="file" id="video" name="video" accept="video/*" required class="w-full text-gray-700">
                                <p class="text-sm text-gray-500 mt-1">Format: MP4, WebM, MOV, AVI. Max ukuran 50MB.</p>
                            </div>
                        </div>
                        <div>
                            <div class="mb-4">
                                <label for="visi" class="block text-gray-700 font-medium mb-2">Visi</label>
                                <textarea id="visi" name="visi" rows="4" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="misi" class="block text-gray-700 font-medium mb-2">Misi</label>
                                <textarea id="misi" name="misi" rows="6" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="program_kerja" class="block text-gray-700 font-medium mb-2">Program Kerja</label>
                                <textarea id="program_kerja" name="program_kerja" rows="8" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" id="submitBtn" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded-full hover:bg-indigo-700 transition-colors">
                            Simpan Kandidat
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const foto = document.getElementById('foto');
            const video = document.getElementById('video');
            const submitBtn = document.getElementById('submitBtn');
            
            // Validasi ukuran file foto
            if (foto.files[0]) {
                if (foto.files[0].size > 5 * 1024 * 1024) {
                    alert('Ukuran foto terlalu besar. Maksimal 5MB.');
                    e.preventDefault();
                    return;
                }
            }
            
            // Validasi ukuran file video
            if (video.files[0]) {
                if (video.files[0].size > 50 * 1024 * 1024) {
                    alert('Ukuran video terlalu besar. Maksimal 50MB.');
                    e.preventDefault();
                    return;
                }
            }
            
            // Tampilkan loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Memproses...';
        });
    </script>
</body>
</html>