<?php
session_start();
require_once '../config/koneksi.php';
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}
// Ambil hasil voting
$sql_results = "SELECT nama_lengkap, jumlah_suara, foto_path FROM kandidat ORDER BY jumlah_suara DESC";
$result_results = $koneksi->query($sql_results);

$all_results = [];
if ($result_results->num_rows > 0) {
    while ($row = $result_results->fetch_assoc()) {
        $all_results[] = $row;
    }
}

// Hitung total suara yang masuk dari siswa dan guru
$sql_total_votes_siswa = "SELECT COUNT(id) AS total FROM pemilih WHERE status_memilih = 1";
$result_total_votes_siswa = $koneksi->query($sql_total_votes_siswa);
$row_total_votes_siswa = $result_total_votes_siswa->fetch_assoc();
$total_votes_siswa = $row_total_votes_siswa['total'];

$sql_total_votes_guru = "SELECT COUNT(id) AS total FROM guru WHERE status_memilih = 1";
$result_total_votes_guru = $koneksi->query($sql_total_votes_guru);
$row_total_votes_guru = $result_total_votes_guru->fetch_assoc();
$total_votes_guru = $row_total_votes_guru['total'];

$total_votes = $total_votes_siswa + $total_votes_guru;

// Hitung total pemilih terdaftar dari siswa dan guru
$sql_total_voters_siswa = "SELECT COUNT(id) AS total FROM pemilih";
$result_total_voters_siswa = $koneksi->query($sql_total_voters_siswa);
$row_total_voters_siswa = $result_total_voters_siswa->fetch_assoc();
$total_voters_siswa = $row_total_voters_siswa['total'];

$sql_total_voters_guru = "SELECT COUNT(id) AS total FROM guru";
$result_total_voters_guru = $koneksi->query($sql_total_voters_guru);
$row_total_voters_guru = $result_total_voters_guru->fetch_assoc();
$total_voters_guru = $row_total_voters_guru['total'];

$total_voters = $total_voters_siswa + $total_voters_guru;

$ketos_terpilih = $all_results[0] ?? null;
$waketos_terpilih = $all_results[1] ?? null;

// Ambil daftar pemilih yang sudah memilih (siswa)
$sql_voted_siswa = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 1 ORDER BY nama_lengkap ASC";
$result_voted_siswa = $koneksi->query($sql_voted_siswa);

$voted_list_siswa = [];
if ($result_voted_siswa->num_rows > 0) {
    while ($row = $result_voted_siswa->fetch_assoc()) {
        $voted_list_siswa[] = $row;
    }
}

// Ambil daftar guru yang sudah memilih
$sql_voted_guru = "SELECT nama_lengkap, nik, jabatan FROM guru WHERE status_memilih = 1 ORDER BY nama_lengkap ASC";
$result_voted_guru = $koneksi->query($sql_voted_guru);

$voted_list_guru = [];
if ($result_voted_guru->num_rows > 0) {
    while ($row = $result_voted_guru->fetch_assoc()) {
        $voted_list_guru[] = $row;
    }
}

// Gabungkan daftar yang sudah memilih
$voted_list = array_merge($voted_list_siswa, $voted_list_guru);

// Ambil daftar pemilih yang belum memilih (siswa)
$sql_not_voted_siswa = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 0 ORDER BY nama_lengkap ASC";
$result_not_voted_siswa = $koneksi->query($sql_not_voted_siswa);

$not_voted_list_siswa = [];
if ($result_not_voted_siswa->num_rows > 0) {
    while ($row = $result_not_voted_siswa->fetch_assoc()) {
        $not_voted_list_siswa[] = $row;
    }
}

// Ambil daftar guru yang belum memilih
$sql_not_voted_guru = "SELECT nama_lengkap, nik, jabatan FROM guru WHERE status_memilih = 0 ORDER BY nama_lengkap ASC";
$result_not_voted_guru = $koneksi->query($sql_not_voted_guru);

$not_voted_list_guru = [];
if ($result_not_voted_guru->num_rows > 0) {
    while ($row = $result_not_voted_guru->fetch_assoc()) {
        $not_voted_list_guru[] = $row;
    }
}

// Gabungkan daftar yang belum memilih
$not_voted_list = array_merge($not_voted_list_siswa, $not_voted_list_guru);

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Hasil - Admin BiVOTE</title>
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
                    <h1 class="text-3xl font-bold text-gray-800">Laporan & Hasil Voting</h1>
                    <p class="text-gray-600 mt-1">Laporan terperinci mengenai hasil pemungutan suara.</p>
                </div>
                <a href="export_pdf2.php" target="_blank" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-full hover:bg-indigo-700 transition-colors">
                    Ekspor ke PDF
                </a>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Hasil Pemilihan Sementara</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php if ($ketos_terpilih): ?>
                        <div class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl">
                            <img src="<?php echo htmlspecialchars($ketos_terpilih['foto_path']); ?>" alt="Foto Ketua Terpilih" class="w-28 h-28 object-cover rounded-full mb-3 shadow">
                            <h3 class="text-lg font-semibold text-gray-800">Ketua Terpilih</h3>
                            <p class="text-xl font-bold text-indigo-600"><?php echo htmlspecialchars($ketos_terpilih['nama_lengkap']); ?></p>
                            <p class="text-gray-500">Suara: <?php echo htmlspecialchars($ketos_terpilih['jumlah_suara']); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($waketos_terpilih): ?>
                        <div class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl">
                            <img src="<?php echo htmlspecialchars($waketos_terpilih['foto_path']); ?>" alt="Foto Wakil Ketua Terpilih" class="w-28 h-28 object-cover rounded-full mb-3 shadow">
                            <h3 class="text-lg font-semibold text-gray-800">Wakil Ketua Terpilih</h3>
                            <p class="text-xl font-bold text-indigo-600"><?php echo htmlspecialchars($waketos_terpilih['nama_lengkap']); ?></p>
                            <p class="text-gray-500">Suara: <?php echo htmlspecialchars($waketos_terpilih['jumlah_suara']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Persentase Hasil Suara</h2>
    <p class="text-sm text-gray-600 mb-4">Total suara masuk: <?php echo $total_votes; ?> dari <?php echo $total_voters; ?> pemilih terdaftar</p>
    <div class="space-y-4">
        <?php
        // LANGKAH 1: Hitung total akumulasi suara dari semua kandidat
        // Ini adalah cara efisien untuk menjumlahkan kolom 'jumlah_suara' dari array $all_results
        $total_akumulasi_suara = array_sum(array_column($all_results, 'jumlah_suara'));

        foreach ($all_results as $kandidat):
            // LANGKAH 2: Hitung persentase menggunakan pembagi yang benar
            // Pastikan total akumulasi suara tidak nol untuk menghindari error pembagian dengan nol
            $percentage = ($total_akumulasi_suara > 0) ? ($kandidat['jumlah_suara'] / $total_akumulasi_suara) * 100 : 0;
        ?>
            <div class="flex items-center space-x-4">
                <div class="w-40 text-right font-medium text-gray-700"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></div>
                <div class="flex-1 bg-gray-200 rounded-full h-6">
                    <div class="bg-indigo-600 h-6 rounded-full" style="width: <?php echo round($percentage, 2); ?>%;"></div>
                </div>
                <span class="w-16 font-bold text-gray-800"><?php echo round($percentage, 2); ?>%</span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Status Memilih</h2>
                <?php 
                    $votedCount = (int)$total_votes; 
                    $notVotedCount = max((int)$total_voters - $votedCount, 0);
                    $votedPercent = ($total_voters > 0) ? round(($votedCount / $total_voters) * 100, 2) : 0;
                    $notVotedPercent = ($total_voters > 0) ? round(($notVotedCount / $total_voters) * 100, 2) : 0;
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="p-6 bg-gray-50 rounded-2xl text-center">
                        <div class="text-4xl font-extrabold text-indigo-700 mb-2"><?php echo $votedPercent; ?>%</div>
                        <div class="text-gray-700 font-semibold">Sudah Memilih</div>
                        <div class="text-sm text-gray-500 mt-1"><?php echo $votedCount; ?> dari <?php echo $total_voters; ?> orang</div>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl text-center">
                        <div class="text-4xl font-extrabold text-gray-700 mb-2"><?php echo $notVotedPercent; ?>%</div>
                        <div class="text-gray-700 font-semibold">Belum Memilih</div>
                        <div class="text-sm text-gray-500 mt-1"><?php echo $notVotedCount; ?> dari <?php echo $total_voters; ?> orang</div>
                    </div>
                </div>
                
                <!-- Statistik Terpisah untuk Siswa dan Guru -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-blue-50 rounded-2xl">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4">Siswa</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Siswa:</span>
                                <span class="font-semibold"><?php echo $total_voters_siswa; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Sudah Memilih:</span>
                                <span class="font-semibold text-green-600"><?php echo $total_votes_siswa; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Belum Memilih:</span>
                                <span class="font-semibold text-red-600"><?php echo $total_voters_siswa - $total_votes_siswa; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-green-50 rounded-2xl">
                        <h3 class="text-lg font-semibold text-green-800 mb-4">Guru</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Guru:</span>
                                <span class="font-semibold"><?php echo $total_voters_guru; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Sudah Memilih:</span>
                                <span class="font-semibold text-green-600"><?php echo $total_votes_guru; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Belum Memilih:</span>
                                <span class="font-semibold text-red-600"><?php echo $total_voters_guru - $total_votes_guru; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>