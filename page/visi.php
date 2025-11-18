<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'Pengguna';
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'siswa';
$user_jabatan = isset($_SESSION['user_jabatan']) ? $_SESSION['user_jabatan'] : '';

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

$candidateId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selectedCandidate = null;

if ($candidateId) {
    $stmt = $koneksi->prepare("SELECT id, nama_lengkap, foto_path, visi, misi, kejar, usia, video_path FROM kandidat WHERE id = ?");
    $stmt->bind_param("i", $candidateId);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedCandidate = $result->fetch_assoc();
    $stmt->close();

    if ($selectedCandidate) {
        $selectedCandidate['misi'] = explode(';', $selectedCandidate['misi']);
    }
}

if (!$selectedCandidate) {
    header("Location: dashboard.php");
    exit;
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Kandidat - BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        .back-button {
            color: #4A148C;
            font-size: 2.5rem;
            position: absolute;
            top: 1rem;
            left: 1rem;
        }
        .vote-btn:disabled {
            background-color: #9CA3AF !important;
            cursor: not-allowed;
            color: #fff !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <main class="container mx-auto p-4 md:p-8">
        <a href="dashboard.php" class="back-button hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </a>
        <div class="flex flex-col items-center text-center mt-12 md:mt-0">
            <div class="w-32 h-32 md:w-40 md:h-40 bg-gray-300 rounded-full overflow-hidden mb-4 shadow-lg">
                <img src="<?php echo htmlspecialchars($selectedCandidate['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($selectedCandidate['nama_lengkap']); ?>" class="w-full h-full object-cover">
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($selectedCandidate['nama_lengkap']); ?></h1>
            <div class="text-gray-600 space-y-1 mb-4">
                <p class="flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    <?php echo htmlspecialchars($selectedCandidate['kejar']); ?>
                </p>
                <p class="flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    <?php echo htmlspecialchars($selectedCandidate['usia']); ?> Tahun
                </p>
            </div>
        </div>
        
        <div class="mt-8 mb-8 p-4 bg-white rounded-2xl shadow-lg w-full h-auto" style="min-height: 400px;">
            <?php if (!empty($selectedCandidate['video_path'])): ?>
                <div class="relative w-full h-0" style="padding-bottom: 56.25%;">
                    <iframe
                        class="absolute inset-0 w-full h-full rounded-2xl"
                        src="<?php echo htmlspecialchars($selectedCandidate['video_path']); ?>"
                        title="Video Visi Misi Kandidat"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-full text-gray-400 text-lg">
                    <p>Video visi misi belum tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">VISI</h2>
            <p class="text-gray-700 leading-relaxed mb-6"><?php echo htmlspecialchars($selectedCandidate['visi']); ?></p>
            
            <h2 class="text-3xl font-bold text-gray-800 mb-4">MISI</h2>
            <ul class="text-gray-700 leading-relaxed list-inside">
                <?php foreach ($selectedCandidate['misi'] as $misiItem): ?>
                    <li class="mb-2 flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="flex-1"><?php echo htmlspecialchars($misiItem); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="flex justify-center gap-4 mb-8">
            <a href="dashboard.php" class="bg-gray-200 text-gray-800 font-semibold py-4 px-8 rounded-full text-lg shadow hover:bg-gray-300 transition-colors">Kembali</a>
            <button id="vote-btn" class="bg-indigo-600 text-white font-bold py-4 px-12 rounded-full text-xl shadow-lg <?php echo $has_voted ? 'disabled' : 'hover:bg-indigo-700'; ?> transition-colors" data-id="<?php echo $selectedCandidate['id']; ?>" data-nama="<?php echo htmlspecialchars($selectedCandidate['nama_lengkap']); ?>" <?php echo $has_voted ? 'disabled' : ''; ?>>
                <?php echo $has_voted ? 'Sudah Vote' : 'VOTE'; ?>
            </button>
        </div>
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
                <?php if ($user_type === 'guru'): ?>
                    <button id="confirm-vote-btn" disabled class="py-2 px-6 bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors">Ya, Saya Yakin</button>
                <?php else: ?>
                    <a id="confirm-vote-link" href="#" class="py-2 px-6 bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors">Ya, Saya Yakin</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Expose user type and jabatan to client-side
        const USER_TYPE = '<?php echo $user_type; ?>';
        const USER_JABATAN = '<?php echo addslashes($user_jabatan); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            const voteButton = document.getElementById('vote-btn');
            const voteModal = document.getElementById('vote-modal');
            const candidateNameToVote = document.getElementById('candidate-name-to-vote');
            const closeVoteModal = document.getElementById('close-vote-modal');
            const confirmVoteLink = document.getElementById('confirm-vote-link');
            const confirmVoteBtn = document.getElementById('confirm-vote-btn');

            let currentCandidateId = null;

            voteButton.addEventListener('click', function() {
                const candidateName = this.getAttribute('data-nama');
                const candidateId = this.getAttribute('data-id');

                currentCandidateId = candidateId;
                candidateNameToVote.textContent = candidateName;

                if (USER_TYPE === 'guru') {
                    // reset checkbox and disable confirm button initially
                    const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                    if (guruCheckbox) {
                        guruCheckbox.checked = false;
                    }
                    if (confirmVoteBtn) {
                        confirmVoteBtn.disabled = true;
                    }
                } else {
                    if (confirmVoteLink) confirmVoteLink.href = `../api/vote_handler.php?id=${candidateId}`;
                }

                voteModal.classList.remove('hidden');
            });

            closeVoteModal.addEventListener('click', function() {
                voteModal.classList.add('hidden');
                currentCandidateId = null;
                if (USER_TYPE === 'guru') {
                    const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                    if (guruCheckbox) guruCheckbox.checked = false;
                    if (confirmVoteBtn) confirmVoteBtn.disabled = true;
                }
            });

            window.onclick = function(event) {
                if (event.target === voteModal) {
                    voteModal.classList.add('hidden');
                }
            };

            // If guru, wire checkbox and confirm button
            if (USER_TYPE === 'guru') {
                const guruCheckbox = document.getElementById('guru-confirm-checkbox');
                if (guruCheckbox && confirmVoteBtn) {
                    guruCheckbox.addEventListener('change', function() {
                        confirmVoteBtn.disabled = !this.checked;
                    });

                    confirmVoteBtn.addEventListener('click', function() {
                        if (currentCandidateId) {
                            window.location.href = `../api/vote_handler.php?id=${currentCandidateId}`;
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>