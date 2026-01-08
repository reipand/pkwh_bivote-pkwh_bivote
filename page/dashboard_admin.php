<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}
$kandidat_with_proker = [
    [
        'id' => 1,
        'nama_lengkap' => 'Qanita Malila',
        'foto_path' => '../assets/image/kandidat_1_1756265965.png', // Ganti dengan path foto yang sesuai
        'proker' => [
            ['judul' => 'Festival Seni & Budaya Tahunan', 'deskripsi' => 'Mengadakan acara besar untuk menampilkan bakat siswa dalam seni musik, tari, dan teater.'],
            ['judul' => 'Program "Jumat Bersih"', 'deskripsi' => 'Mengaktifkan kembali kegiatan kebersihan lingkungan sekolah setiap hari Jumat pagi.'],
            ['judul' => 'Digitalisasi Majalah Dinding', 'deskripsi' => 'Membuat platform mading online agar informasi lebih cepat diakses oleh semua siswa.'],
        ]
    ],
    [
        'id' => 2,
        'nama_lengkap' => 'Safdiza Azizi',
        'foto_path' => '../assets/image/kandidat_3_1756265984.png', // Ganti dengan path foto yang sesuai
        'proker' => [
            ['judul' => 'Olimpiade Akademik Antar Kelas', 'deskripsi' => 'Meningkatkan semangat kompetisi akademik melalui olimpiade internal sekolah.'],
            ['judul' => 'Workshop Keterampilan Digital', 'deskripsi' => 'Mengadakan pelatihan coding, desain grafis, dan video editing untuk siswa.'],
            ['judul' => 'Gerakan Donasi Buku', 'deskripsi' => 'Mengumpulkan dan menyumbangkan buku untuk perpustakaan sekolah dan panti asuhan.'],
        ]
    ],
    [
        'id' => 3,
        'nama_lengkap' => 'Aura Anastasya',
        'foto_path' => '../assets/image/kandidat_2_1756266166.jpg', // Ganti dengan path foto yang sesuai
        'proker' => [
            ['judul' => 'Pekan Olahraga & Kesehatan', 'deskripsi' => 'Menyelenggarakan kompetisi olahraga antar kelas dan seminar kesehatan mental.'],
            ['judul' => 'Program Mentoring Belajar', 'deskripsi' => 'Membuat sistem di mana kakak kelas dapat membantu adik kelas dalam pelajaran tertentu.'],
            ['judul' => 'Podcast Sekolah "Suara Siswa"', 'deskripsi' => 'Membuat media podcast sebagai wadah aspirasi dan kreativitas siswa.'],
        ]
    ],
    [
        'id' => 3,
        'nama_lengkap' => 'Samudera',
        'foto_path' => '../assets/image/kandidat_4_1756266005.jpg', // Ganti dengan path foto yang sesuai
        'proker' => [
            ['judul' => 'Program Mentoring Belajar', 'deskripsi' => 'Membuat sistem di mana kakak kelas dapat membantu adik kelas dalam pelajaran tertentu.'],
            ['judul' => 'Podcast Sekolah "Suara Siswa"', 'deskripsi' => 'Membuat media podcast sebagai wadah aspirasi dan kreativitas siswa.'],
        ]
    ],
];

// $kandidat_data = [
//     [
//         'id' => 1,
//         'nama_lengkap' => 'Qanita Malila',
//         'foto_path' => '../assets/image/qani.png', // Ini foto profil untuk kartu utama
//         'poster_path' => '../assets/image/poster_qanita.jpg' // INI POSTER UNTUK MODAL
//     ],
//     [
//         'id' => 2,
//         'nama_lengkap' => 'Safdiza Azizi',
//         'foto_path' => '../assets/image/diza.png',
//         'poster_path' => '../assets/image/poster_safdiza.jpg' // INI POSTER UNTUK MODAL
//     ],
//     [
//         'id' => 3,
//         'nama_lengkap' => 'Aura Anastasya',
//         'foto_path' => '../assets/image/aura.png',
//         'poster_path' => '../assets/image/poster_aura.jpg' // INI POSTER UNTUK MODAL
//     ],
//     [
//         'id' => 4, // Perbaikan: ID kandidat sebaiknya unik, saya ganti menjadi 4
//         'nama_lengkap' => 'Dio',
//         'foto_path' => '../assets/image/dio.png',
//         'poster_path' => '../assets/image/poster_dio.jpg' // INI POSTER UNTUK MODAL
//     ],
// ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .stat-card { transition: box-shadow 0.2s; }
        .stat-card:hover { box-shadow: 0 8px 32px 0 rgba(49,46,129,0.12);}
        .bar-animate { transition: width 0.7s cubic-bezier(.4,0,.2,1);}
        .chart-circle {
            transform: rotate(-90deg);
        }
        .chart-circle-progress {
            transition: stroke-dashoffset 0.7s cubic-bezier(.4,0,.2,1);
        }
        
/* Styling untuk Modal dan Carousel */
.modal-overlay {
    display: none; /* Awalnya disembunyikan */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    align-items: center;
    justify-content: center;
}

.modal-content {
    position: relative;
    background-color: #fefefe;
    margin: auto;
    padding: 30px;
    border-radius: 24px;
    width: 90%;
    max-width: 800px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.carousel-container {
    position: relative;
    overflow: hidden;
}

.carousel-slide {
    display: none; /* Sembunyikan semua slide secara default */
    animation: slideIn 0.5s ease-in-out;
}

.carousel-slide.active {
    display: block; /* Tampilkan hanya slide yang aktif */
}

@keyframes slideIn {
    from { opacity: 0.5; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(255, 255, 255, 0.7);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.carousel-nav:hover {
    background-color: white;
    color: #4f46e5; /* Indigo color */
}
.carousel-nav.prev { left: -15px; }
.carousel-nav.next { right: -15px; }

@media (max-width: 768px) {
    .carousel-nav.prev { left: 5px; }
    .carousel-nav.next { right: 5px; }
    .modal-content { padding: 20px; }
}
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen">
        <?php include '../includes/header_admin.php'; ?>

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Hello, Admin!</h1>
                <p class="text-gray-600 mt-1">Welcome To <span class="font-bold text-indigo-700">BiVote</span></p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="col-span-1 bg-white p-6 rounded-3xl shadow-lg stat-card flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-2xl font-bold text-blue-800" id="total-voters">0</span>
                            <svg width="80" height="80" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8"></circle>
                                <circle id="circle-total-voters" class="chart-circle" cx="50" cy="50" r="45" fill="none" stroke="#2563eb" stroke-width="8" stroke-dasharray="282.7" stroke-dashoffset="282.7"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="text-gray-700 font-semibold">Total Registered Voters</div>
                            <div class="text-xs text-gray-400">Semua siswa yang terdaftar</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-2xl font-bold text-blue-800" id="total-votes">0</span>
                            <svg width="80" height="80" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8"></circle>
                                <circle id="circle-total-votes" class="chart-circle" cx="50" cy="50" r="45" fill="none" stroke="#2563eb" stroke-width="8" stroke-dasharray="282.7" stroke-dashoffset="282.7"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="text-gray-700 font-semibold">Total Votes</div>
                            <div class="text-xs text-gray-400">Jumlah suara masuk</div>
                        </div>
                    </div>
                </div>

                <!-- //poster versi -->
                <!-- <div class="col-span-1 lg:col-span-2 bg-white p-6 rounded-3xl shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">Poster Program Kerja</h3>
                    <p class="text-gray-500 mb-6">Lihat rangkuman program kerja dari setiap calon Ketua OSIS 2025–2026.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($kandidat_data as $index => $kandidat): /* Menggunakan variabel baru */ ?>
                            <div class="flex flex-col items-center text-center p-4 bg-gray-50 rounded-2xl border">
                                <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-24 h-24 object-cover rounded-full mb-3 shadow-md">
                                <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></h4>
                                <button data-index="<?php echo $index; ?>" class="open-modal-btn mt-4 bg-indigo-600 text-white font-medium py-2 px-5 rounded-full hover:bg-indigo-700 transition-colors text-sm">
                                    Lihat Poster
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="prokerModal" class="modal-overlay">
                    <div class="modal-content">
                        <button id="closeModalBtn" class="absolute top-4 right-5 text-gray-500 hover:text-gray-800 text-4xl font-light z-20">&times;</button>
                        
                        <div class="carousel-container">
                            <?php foreach ($kandidat_data as $kandidat): /* Menggunakan variabel baru */ ?>
                                <div class="carousel-slide">
                                    <img src="<?php echo htmlspecialchars($kandidat['poster_path']); ?>" 
                                        alt="Poster Program Kerja <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" 
                                        class="w-full h-auto rounded-lg object-contain max-h-[75vh]">
                                </div>
                            <?php endforeach; ?>

                            <button id="prevBtn" class="carousel-nav prev">&#10094;</button>
                            <button id="nextBtn" class="carousel-nav next">&#10095;</button>
                        </div>
                    </div>
                </div> -->
                <!-- /versi teks -->
                <div class="col-span-1 lg:col-span-2 bg-white p-6 rounded-3xl shadow-lg">
    <h3 class="text-2xl font-bold text-gray-800 mb-1">Program Kerja Kandidat</h3>
    <p class="text-gray-500 mb-6">Lihat visi, misi, dan program kerja dari setiap calon Ketua OSIS 2025–2026.</p>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($kandidat_with_proker as $index => $kandidat): ?>
            <div class="flex flex-col items-center text-center p-4 bg-gray-50 rounded-2xl border">
                <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-24 h-24 object-cover rounded-full mb-3 shadow-md">
                <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></h4>
                <button data-index="<?php echo $index; ?>" class="open-modal-btn mt-4 bg-indigo-600 text-white font-medium py-2 px-5 rounded-full hover:bg-indigo-700 transition-colors text-sm">
                    Lihat Program Kerja
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="prokerModal" class="modal-overlay">
    <div class="modal-content">
        <button id="closeModalBtn" class="absolute top-4 right-5 text-gray-500 hover:text-gray-800 text-4xl font-light">&times;</button>
        
        <div class="carousel-container">
            <?php foreach ($kandidat_with_proker as $kandidat): ?>
                <div class="carousel-slide">
                    <div class="flex flex-col md:flex-row items-center gap-6 md:gap-8 p-4">
                        <div class="flex-shrink-0 text-center">
                            <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="<?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-full mx-auto shadow-xl border-4 border-white">
                            <h3 class="text-xl font-bold text-gray-900 mt-4"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></h3>
                            <p class="text-indigo-600 font-semibold">Calon Ketua OSIS</p>
                        </div>
                        <div class="w-full text-left">
                            <h4 class="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Program Kerja Unggulan:</h4>
                            <ul class="space-y-3 list-disc list-inside text-gray-700">
                                <?php foreach ($kandidat['proker'] as $proker): ?>
                                    <li>
                                        <span class="font-semibold"><?php echo htmlspecialchars($proker['judul']); ?>:</span>
                                        <?php echo htmlspecialchars($proker['deskripsi']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <button id="prevBtn" class="carousel-nav prev">&#10094;</button>
            <button id="nextBtn" class="carousel-nav next">&#10095;</button>
        </div>
    </div>
</div>

    
    <!-- <div class="absolute inset-0 bg-no-repeat bg-right-bottom opacity-20 z-0 pointer-events-none" style="background-image: url('../assets/image/admin_illustration.jpg');"></div> -->
</div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-8 stat-card">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-lg font-bold text-gray-800">Live Results</span>
                    <span class="text-gray-500 text-xs">Calon Ketua Osis</span>
                </div>
                <div class="h-96">
                    <canvas id="liveResultsChart"></canvas>
                </div>
                <p class="mt-4 text-xs text-red-500">Real Time</p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-8 stat-card">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-lg font-bold text-gray-800">Status Memilih</span>
                    <span class="text-gray-500 text-xs">Sudah vs Belum Memilih</span>
                </div>
                <div class="h-80 flex items-center justify-center">
                    <canvas id="voteStatusChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('prokerModal');
    const openModalBtns = document.querySelectorAll('.open-modal-btn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    const slides = document.querySelectorAll('.carousel-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    let currentIndex = 0;
    const totalSlides = slides.length;

    function showSlide(index) {
        // Sembunyikan semua slide
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        // Tampilkan slide yang dipilih
        slides[index].classList.add('active');
    }

    function openModal(index) {
        currentIndex = index;
        showSlide(currentIndex);
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    // Event listener untuk setiap tombol "Lihat Program Kerja"
    openModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            openModal(index);
        });
    });

    // Event listener untuk tombol close modal
    closeModalBtn.addEventListener('click', closeModal);

    // Event listener untuk menutup modal saat klik di luar area konten
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            closeModal();
        }
    });

    // Event listener untuk tombol navigasi carousel
    nextBtn.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    });

    prevBtn.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        showSlide(currentIndex);
    });
});
    let myChart;
    let statusChart;
    const colors = ['#4A148C', '#6A5ACD', '#7B68EE', '#9370DB', '#BA55D3', '#DA70D6'];

    function renderVoteResults(data) {
    if (data.status !== 'success') return;

    const totalVoters = data.total_voters || 0;
    const totalVotes = data.total_votes || 0;
    
    document.getElementById('total-voters').textContent = totalVoters;
    document.getElementById('total-votes').textContent = totalVotes;
    // elemen New Registrations dihapus

    const circumference = 2 * Math.PI * 45;
    
    document.getElementById('circle-total-voters').style.strokeDashoffset = 0;

    const percentVotes = totalVoters > 0 ? (totalVotes / totalVoters) * 100 : 0;
    const offsetVotes = circumference - (percentVotes / 100) * circumference;
    document.getElementById('circle-total-votes').style.strokeDashoffset = offsetVotes;
    
    // perhitungan dan update untuk New Registrations dihapus

        // Chart hasil vote
        // Hitung total suara untuk persentase
        let totalAllVotes = data.all_results.reduce((sum, res) => sum + parseInt(res.jumlah_suara), 0);
        
        const labels = data.all_results.map(res => res.nama_lengkap);
        const votes = data.all_results.map(res => parseInt(res.jumlah_suara));
        
        if (myChart) {
            myChart.data.labels = labels;
            myChart.data.datasets[0].data = votes;
            myChart.data.datasets[0].backgroundColor = labels.map((_, i) => colors[i % colors.length]);
            myChart.update();
        } else {
            const ctx = document.getElementById('liveResultsChart').getContext('2d');
            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Suara',
                        data: votes,
                        backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        let percentage = (context.parsed.y / totalAllVotes * 100).toFixed(2) + '%';
                                        label += context.parsed.y + ' (' + percentage + ')';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        // Render/Update pie chart status memilih
        const notVoted = Math.max(totalVoters - totalVotes, 0);
        const statusCtx = document.getElementById('voteStatusChart').getContext('2d');
        const pieColors = ['#4A148C', '#E5E7EB'];
        if (statusChart) {
            statusChart.data.datasets[0].data = [totalVotes, notVoted];
            statusChart.update();
        } else {
            statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Sudah Memilih', 'Belum Memilih'],
                    datasets: [{
                        data: [totalVotes, notVoted],
                        backgroundColor: pieColors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.raw || 0;
                                    const percent = totalVoters > 0 ? (val / totalVoters * 100).toFixed(2) : 0;
                                    return `${ctx.label}: ${val} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Polling setiap 3 detik
    setInterval(function() {
        fetch('../api/get_results.php')
            .then(res => res.json())
            .then(data => renderVoteResults(data));
    }, 3000);

    // Inisialisasi pertama
    fetch('../api/get_results.php')
        .then(res => res.json())
        .then(data => renderVoteResults(data));
    </script>
</body>
</html>