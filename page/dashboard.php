<?php
session_start();
require_once '../config/koneksi.php';

// Periksa apakah pengguna sudah login, jika tidak, redirect ke halaman login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil ID pengguna dari sesi. Menggunakan 'user_id' untuk konsistensi.
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'Pengguna';
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'siswa';
$user_jabatan = isset($_SESSION['user_jabatan']) ? $_SESSION['user_jabatan'] : '';

// Periksa apakah pengguna sudah memberikan suara berdasarkan tipe user
$has_voted = false;
if ($user_type === 'guru') {
    $stmt_check_vote = $koneksi->prepare("SELECT status_memilih FROM guru WHERE id = ?");
} else {
    $stmt_check_vote = $koneksi->prepare("SELECT status_memilih FROM pemilih WHERE id = ?");
}
$stmt_check_vote->bind_param("i", $user_id);
$stmt_check_vote->execute();
$result_check_vote = $stmt_check_vote->get_result();
if ($result_check_vote->num_rows > 0) {
    $voter_status = $result_check_vote->fetch_assoc();
    $has_voted = $voter_status['status_memilih'] == 1;
}
$stmt_check_vote->close();


// Ambil data kandidat dari database untuk ditampilkan di dashboard
$sql = "SELECT id, nama_lengkap, foto_path, visi, misi, program_kerja, kejar, usia FROM kandidat ORDER BY id ASC";
$result = $koneksi->query($sql);

$candidates = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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
    <title>Dashboard - BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        .candidate-card-transition {
            transition: background-image 0.5s ease-in-out, background-color 0.5s ease-in-out, color 0.5s ease-in-out;
            background-color: white; /* Warna dasar saat tidak di-hover */
            color: black;
        }
        .candidate-card-transition:hover {
            background-image: linear-gradient(135deg, #4A148C 0%, #6A5ACD 100%);
            color: white;
        }
        .candidate-card-transition:hover .vote-btn,
        .candidate-card-transition:hover .visi-misi-btn {
            background-color: white;
            color: #4A148C;
        }
        .candidate-card-transition .vote-btn,
        .candidate-card-transition .visi-misi-btn {
            background-color: #4A148C;
            color: white;
            transition: background-color 0.5s ease-in-out, color 0.5s ease-in-out;
        }
        .vote-btn:disabled {
            background-color: #9CA3AF !important;
            color: #fff !important;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-100 flex">
    <?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 p-8 relative pb-16"> 
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat datang, <?php echo htmlspecialchars($user_nama); ?>!</h1>
        <?php if ($user_type === 'guru'): ?>
            <p class="text-lg text-gray-600 mb-2"><?php echo htmlspecialchars($user_jabatan); ?></p>
            <h2 class="text-xl text-gray-600 mb-6">Pilih Kandidat Ketua OSIS yang Anda Dukung!</h2>
        <?php else: ?>
            <h2 class="text-xl text-gray-600 mb-6">Pilih Kandidat Jagoan mu!</h2>
        <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <?php foreach ($candidates as $kandidat): ?>
            <div class="candidate-card-transition rounded-3xl shadow-lg flex flex-col p-4 relative overflow-hidden">
                <div class="flex items-center mb-4">
                    <div class="bg-gray-200 text-gray-800 text-sm font-semibold py-1 px-4 rounded-full">Kandidat <?php echo $kandidat['id']; ?></div>
                </div>
                <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></h2>
                <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-full h-auto rounded-xl object-cover" style="height: 250px;">
                
                <div class="flex flex-col items-center mt-4">
                    <div class="flex space-x-4">
                        <!-- <button class="vote-btn font-medium py-2 px-6 rounded-full <?php echo $has_voted ? 'disabled' : 'hover:bg-white hover:text-indigo-600'; ?> transition-colors" data-id="<?php echo $kandidat['id']; ?>" data-nama="<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" <?php echo $has_voted ? 'disabled' : ''; ?>>
                            <?php echo $has_voted ? 'Sudah Vote' : 'Vote'; ?>
                        </button> -->
                        <a href="visi.php?id=<?php echo $kandidat['id']; ?>" class="visi-misi-btn flex items-center justify-center font-medium py-2 px-6 rounded-full hover:bg-white hover:text-indigo-600 transition-colors">
                            Visi-Misi
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="text-center text-gray-500 text-xs mt-4 w-full absolute bottom-4 left-0">
        &copy; 2025 BiVOTE. All rights reserved.
    </p>
</main>

    <div id="vote-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
        <div class="relative p-8 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-xl bg-white">
            <h3 class="text-2xl font-bold mb-4">Konfirmasi Pilihan</h3>
            <p id="modal-vote-text" class="text-gray-700 mb-6">Apakah Anda yakin ingin memilih <span class="font-bold" id="candidate-name-to-vote"></span>?</p>
            <?php if ($user_type === 'guru'): ?>
                <p class="text-yellow-700 mb-4">Anda akan memilih sebagai Guru: <?php echo htmlspecialchars($user_jabatan); ?>. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex items-center justify-start mb-4">
                    <input id="guru-confirm-checkbox" type="checkbox" class="mr-2" />
                    <label for="guru-confirm-checkbox" class="text-gray-700">Saya konfirmasi pilihan sebagai Guru</label>
                </div>
            <?php endif; ?>
            <div class="flex justify-end space-x-4">
                <button id="close-vote-modal" class="py-2 px-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors">Batal</button>
                <button id="confirm-vote-btn" class="py-2 px-6 bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors">Ya, Saya Yakin</button>
            </div>
        </div>
    </div>
    

    <script>
    // Expose user type and jabatan to client-side for conditional behavior
    const USER_TYPE = '<?php echo $user_type; ?>';
    const USER_JABATAN = '<?php echo addslashes($user_jabatan); ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const voteButtons = document.querySelectorAll('.vote-btn:not([disabled])');
        const voteModal = document.getElementById('vote-modal');
        const candidateNameToVote = document.getElementById('candidate-name-to-vote');
    const confirmVoteBtn = document.getElementById('confirm-vote-btn');
        const closeVoteModal = document.getElementById('close-vote-modal');
        
        let selectedCandidateId = null;

        voteButtons.forEach(button => {
            button.addEventListener('click', function() {
                selectedCandidateId = this.getAttribute('data-id');
                const candidateName = this.getAttribute('data-nama');
                candidateNameToVote.textContent = candidateName;
                voteModal.classList.remove('hidden');

                // If current user is guru, require explicit confirmation checkbox
                if (USER_TYPE === 'guru') {
                    const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                    if (guruCheckbox) {
                        guruCheckbox.checked = false;
                        confirmVoteBtn.disabled = true;
                    }
                }
            });
        });

        closeVoteModal.addEventListener('click', function() {
            voteModal.classList.add('hidden');
            selectedCandidateId = null;
            if (USER_TYPE === 'guru') {
                const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                if (guruCheckbox) guruCheckbox.checked = false;
                if (confirmVoteBtn) confirmVoteBtn.disabled = true;
            }
        });

        window.addEventListener('click', function(event) {
            if (event.target === voteModal) {
                voteModal.classList.add('hidden');
                selectedCandidateId = null;
            }
        });

        // Jika tombol konfirmasi ada (button), gunakan event click
        if (confirmVoteBtn) {
            // Jika user guru, listen ke checkbox untuk enable/disable
            if (USER_TYPE === 'guru') {
                const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                if (guruCheckbox) {
                    guruCheckbox.addEventListener('change', function() {
                        confirmVoteBtn.disabled = !this.checked;
                    });
                }
            }

            confirmVoteBtn.addEventListener('click', function() {
                if (selectedCandidateId) {
                    window.location.href = `../api/vote_handler.php?id=${selectedCandidateId}`;
                }
            });
        }
    });
    </script>
</body>
</html>