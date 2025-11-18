<?php
session_start();
require_once '../config/koneksi.php';

// Data yang akan digunakan di halaman
$sql_results = "SELECT nama_lengkap, jumlah_suara, foto_path, visi, misi FROM kandidat ORDER BY jumlah_suara DESC";
$result_results = $koneksi->query($sql_results);

$all_results = [];
if ($result_results->num_rows > 0) {
    while ($row = $result_results->fetch_assoc()) {
        $all_results[] = $row;
    }
}

// Hitung total suara yang masuk
$sql_total_votes = "SELECT COUNT(id) AS total FROM pemilih WHERE status_memilih = 1";
$result_total_votes = $koneksi->query($sql_total_votes);
$row_total_votes = $result_total_votes->fetch_assoc();
$total_votes = $row_total_votes['total'];

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Hasil Voting - BiVOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-8">

    <header class="text-center mb-8">
        <h1 class="text-5xl font-extrabold text-indigo-700">Live Results</h1>
        <p class="text-gray-600 mt-2 text-xl">Hasil Pemilihan Ketua OSIS 2025–2026</p>
    </header>

    <div class="bg-white p-8 rounded-3xl shadow-lg w-full max-w-4xl mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Persentase Hasil Suara</h2>
        <p class="text-sm text-gray-600 mb-4">Total suara masuk: <?php echo $total_votes; ?></p>
        <div class="space-y-6">
            <?php foreach ($all_results as $kandidat): ?>
                <?php
                $percentage = ($total_votes > 0) ? ($kandidat['jumlah_suara'] / $total_votes) * 100 : 0;
                ?>
                <div class="flex items-center space-x-4">
                    <img src="<?php echo htmlspecialchars($kandidat['foto_path']); ?>" alt="Foto <?php echo htmlspecialchars($kandidat['nama_lengkap']); ?>" class="w-16 h-16 object-cover rounded-full shadow-md">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800 mb-1"><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></div>
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-4">
                                <div class="bg-indigo-600 h-4 rounded-full transition-all duration-700 ease-in-out" style="width: <?php echo round($percentage, 2); ?>%;"></div>
                            </div>
                            <span class="w-12 ml-4 font-bold text-gray-800 text-sm text-right"><?php echo round($percentage, 2); ?>%</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-lg w-full max-w-4xl h-96 mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Grafik Hasil Voting</h2>
        <canvas id="liveResultsChart"></canvas>
    </div>

    <footer class="text-center text-gray-500 text-xs mt-auto w-full absolute bottom-4 left-0">
        &copy; 2025 BiVOTE. All rights reserved.
    </footer>

    <script>
    let myChart;
    const colors = ['#4A148C', '#6A5ACD', '#7B68EE', '#9370DB', '#BA55D3', '#DA70D6'];

    function renderVoteResults(data) {
        if (data.status !== 'success') return;

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