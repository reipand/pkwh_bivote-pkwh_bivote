<?php
session_start();
require_once '../config/koneksi.php';

// Ambil status sesi dari database
$sql_sesi = "SELECT sesi_aktif FROM debat_sesi_status WHERE id = 1";
$result_sesi = $koneksi->query($sql_sesi);
$sesi_aktif = $result_sesi->fetch_assoc()['sesi_aktif'];

// Periksa apakah user sudah memberikan penilaian di sesi ini
$has_voted_this_session = false;
if (isset($_SESSION['debat_sesi_' . $sesi_aktif . '_voted'])) {
    $has_voted_this_session = true;
}

// Ambil data kandidat dari database
$sql_results = "SELECT id, nama_lengkap, foto_path FROM kandidat ORDER BY id ASC";
$result_results = $koneksi->query($sql_results);

$candidates = [];
if ($result_results->num_rows > 0) {
    while($row = $result_results->fetch_assoc()) {
        $candidates[] = $row;
    }
}
$koneksi->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Kepuasan Debat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        input[type="radio"]:checked + .radio-label {
            border-color: #4A148C;
            background-color: #F3F4F6;
            transform: scale(1.05);
        }
        .radio-label {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-8">

    <div class="bg-white p-8 rounded-3xl shadow-lg w-full max-w-2xl text-center">
        <h1 class="text-3xl font-bold text-indigo-700 mb-2">Form Kepuasan Debat</h1>
        <p class="text-gray-600 mb-6">Berikan penilaian Anda untuk setiap sesi debat.</p>

        <div id="status-message" class="mb-4 text-sm font-medium"></div>

        <?php if ($sesi_aktif <= 3): ?>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sesi Debat ke-<?php echo $sesi_aktif; ?></h2>
            <form id="debat-form-<?php echo $sesi_aktif; ?>" action="../api/submit_kepuasan.php" method="POST" class="space-y-6">
                <input type="hidden" name="sesi" value="<?php echo $sesi_aktif; ?>">

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Pilih Kandidat:</label>
                    <div class="flex flex-wrap justify-center gap-4">
                        <?php foreach ($candidates as $kandidat): ?>
                            <input type="radio" id="kandidat-<?php echo $kandidat['id']; ?>" name="id_kandidat" value="<?php echo $kandidat['id']; ?>" class="absolute opacity-0 peer">
                               <label for="kandidat-<?php echo $kandidat['id']; ?>" class="radio-label cursor-pointer p-4 rounded-xl border-2 hover:border-indigo-600 transition-colors">
                                <div class="flex flex-col items-center">
                                    <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-16 h-16 rounded-full mb-2 object-cover">
                                        <span class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></span>
                                    </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Pilih Tingkat Kepuasan (1-5):</label>
                    <div class="flex justify-center space-x-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="relative">
                                <input type="radio" id="kepuasan-<?php echo $i; ?>" name="nilai_kepuasan" value="<?php echo $i; ?>" class="absolute opacity-0 peer">
                                <label for="kepuasan-<?php echo $i; ?>" class="radio-label flex flex-col items-center p-3 rounded-lg border-2 hover:border-indigo-600 transition-colors cursor-pointer">
                                    <span class="text-xl font-bold text-gray-800"><?php echo $i; ?></span>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-medium py-3 px-6 rounded-full hover:bg-indigo-700 transition-colors">
                    Kirim Penilaian Sesi <?php echo htmlspecialchars($sesi_aktif); ?>
                </button>
            </form>
        <?php elseif ($sesi_aktif > 3): ?>
            <p class="text-xl text-gray-800 font-semibold mt-8">Maaf, debat telah berakhir.</p>
        <?php else: ?>
            <p class="text-xl text-gray-800 font-semibold mt-8">Anda sudah memberikan penilaian untuk sesi ini. Silakan tunggu sesi berikutnya.</p>
        <?php endif; ?>
    </div>
    
    <footer class="text-center text-gray-500 text-xs mt-4">
        &copy; 2025 BiVOTE. All rights reserved.
    </footer>

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const sesi = form.find('input[name="sesi"]').val();
            const kandidatId = form.find('input[name="id_kandidat"]:checked').val();
            const nilaiKepuasan = form.find('input[name="nilai_kepuasan"]:checked').val();
            const statusMessage = $('#status-message');

            if (!kandidatId || !nilaiKepuasan) {
                statusMessage.text('Harap pilih kandidat dan tingkat kepuasan.').removeClass().addClass('text-red-600');
                return;
            }

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: {
                    sesi: sesi,
                    id_kandidat: kandidatId,
                    nilai_kepuasan: nilaiKepuasan
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        statusMessage.text(response.message).removeClass().addClass('text-green-600');
                        setTimeout(function() {
                            // Alihkan ke halaman konfirmasi setelah berhasil
                            window.location.href = 'debat_confirmation.php';
                        }, 1000);
                    } else {
                        statusMessage.text(response.message).removeClass().addClass('text-red-600');
                    }
                },
                error: function() {
                    statusMessage.text('Terjadi kesalahan saat mengirim data.').removeClass().addClass('text-red-600');
                }
            });
        });
    });
    </script>
</body>
</html>