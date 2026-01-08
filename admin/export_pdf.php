<?php
session_start();
// Panggil autoloader Composer
require_once '../vendor/autoload.php';

// Panggil namespace Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: ../page/admin_login.php");
    exit;
}
// ===================================================================
//  LANGKAH A: PENGAMBILAN DATA (Sama seperti halaman laporan)
// ===================================================================

// Ambil hasil voting
$sql_results = "SELECT nama_lengkap, jumlah_suara FROM kandidat ORDER BY jumlah_suara DESC";
$result_results = $koneksi->query($sql_results);
$all_results = [];
if ($result_results->num_rows > 0) {
    while ($row = $result_results->fetch_assoc()) {
        $all_results[] = $row;
    }
}
$total_akumulasi_suara = array_sum(array_column($all_results, 'jumlah_suara'));

// Hitung total suara masuk & pemilih terdaftar
$sql_total_votes = "SELECT COUNT(id) AS total FROM pemilih WHERE status_memilih = 1";
$result_total_votes = $koneksi->query($sql_total_votes);
$total_votes = $result_total_votes->fetch_assoc()['total'];

$sql_total_voters = "SELECT COUNT(id) AS total FROM pemilih";
$result_total_voters = $koneksi->query($sql_total_voters);
$total_voters = $result_total_voters->fetch_assoc()['total'];

// Ambil daftar pemilih (sudah dan belum memilih)
$sql_voted = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 1 ORDER BY nama_lengkap ASC";
$result_voted = $koneksi->query($sql_voted);
$voted_list = [];
if ($result_voted->num_rows > 0) {
    while ($row = $result_voted->fetch_assoc()) {
        $voted_list[] = $row;
    }
}

$sql_not_voted = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 0 ORDER BY nama_lengkap ASC";
$result_not_voted = $koneksi->query($sql_not_voted);
$not_voted_list = [];
if ($result_not_voted->num_rows > 0) {
    while ($row = $result_not_voted->fetch_assoc()) {
        $not_voted_list[] = $row;
    }
}
$koneksi->close();

// ===================================================================
//  LANGKAH B: MEMBUAT TEMPLATE HTML UNTUK PDF
// ===================================================================

ob_start(); // Mulai output buffering
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Voting</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        h1, h2, h3 { text-align: center; margin: 0; }
        h1 { margin-bottom: 5px; }
        h3 { font-weight: normal; }
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-bottom: 20px; text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #888; }
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .header-table img { width: 50px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="footer">
        Laporan Dibuat pada: <?php echo date('d F Y, H:i:s'); ?> - &copy; BiVOTE
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: left;">
                <img src="../assets/image/logo.png" alt="Logo Aplikasi">
            </td>
            <td style="width: 50%; text-align: center;">
                <h3>Aplikasi Sistem Informasi Pemilihan Ketua OSIS</h3>
                <h1>Laporan & Hasil Voting</h1>
            </td>
            <td style="width: 25%; text-align: right;">
                <img src="../assets/image/bi.png" alt="Logo BI">
            </td>
        </tr>
    </table>

    <div class="summary">
        Total suara masuk: <strong><?php echo $total_votes; ?></strong> dari <strong><?php echo $total_voters; ?></strong> pemilih terdaftar.
    </div>

    <h2>Persentase Hasil Suara</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Kandidat</th>
                <th>Jumlah Suara</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_results as $kandidat): ?>
                <?php $percentage = ($total_akumulasi_suara > 0) ? ($kandidat['jumlah_suara'] / $total_akumulasi_suara) * 100 : 0; ?>
                <tr>
                    <td><?php echo htmlspecialchars($kandidat['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($kandidat['jumlah_suara']); ?></td>
                    <td><?php echo round($percentage, 2); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Pemilih Sudah Memberikan Suara (<?php echo count($voted_list); ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>NIS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($voted_list) > 0): ?>
                <?php foreach ($voted_list as $voter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($voter['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($voter['nis']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" style="text-align:center;">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Pemilih Belum Memberikan Suara (<?php echo count($not_voted_list); ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>NIS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($not_voted_list) > 0): ?>
                <?php foreach ($not_voted_list as $voter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($voter['nama_lengkap']); ?></td>
                        <td><?php htmlspecialchars($voter['nis']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" style="text-align:center;">Semua pemilih sudah memilih.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();

// ===================================================================
//  LANGKAH C: PROSES GENERATE PDF MENGGUNAKAN DOMPDF
// ===================================================================

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("laporan-hasil-voting-" . date("Y-m-d") . ".pdf", ["Attachment" => true]);

?>