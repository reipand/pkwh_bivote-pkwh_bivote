<?php
session_start();
// 1. Panggil autoloader Composer
require_once '../vendor/autoload.php';

// Panggil namespace FPDI
use setasign\Fpdi\Fpdi;

require_once '../config/koneksi.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// ===================================================================
//  LANGKAH A: PENGAMBILAN DATA
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

// Ambil daftar pemilih siswa (sudah dan belum memilih)
$sql_voted = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 1 AND role = 'siswa' ORDER BY nama_lengkap ASC";
$result_voted = $koneksi->query($sql_voted);
$voted_list = [];
if ($result_voted->num_rows > 0) {
    while ($row = $result_voted->fetch_assoc()) {
        $voted_list[] = $row;
    }
}

$sql_not_voted = "SELECT nama_lengkap, nis FROM pemilih WHERE status_memilih = 0 AND role = 'siswa' ORDER BY nama_lengkap ASC";
$result_not_voted = $koneksi->query($sql_not_voted);
$not_voted_list = [];
if ($result_not_voted->num_rows > 0) {
    while ($row = $result_not_voted->fetch_assoc()) {
        $not_voted_list[] = $row;
    }
}

// Ambil daftar pemilih guru (sudah dan belum memilih)
$sql_voted_guru = "SELECT nama_lengkap, nik FROM guru WHERE status_memilih = 1 ORDER BY nama_lengkap ASC";
$result_voted_guru = $koneksi->query($sql_voted_guru);
$voted_list_guru = [];
if ($result_voted_guru->num_rows > 0) {
    while ($row = $result_voted_guru->fetch_assoc()) {
        $voted_list_guru[] = $row;
    }
}

$sql_not_voted_guru = "SELECT nama_lengkap, nik FROM guru WHERE status_memilih = 0 ORDER BY nama_lengkap ASC";
$result_not_voted_guru = $koneksi->query($sql_not_voted_guru);
$not_voted_list_guru = [];
if ($result_not_voted_guru->num_rows > 0) {
    while ($row = $result_not_voted_guru->fetch_assoc()) {
        $not_voted_list_guru[] = $row;
    }
}
$koneksi->close();

// ===================================================================
//  LANGKAH B: PROSES GENERATE PDF MENGGUNAKAN FPDI
// ===================================================================

// Buat instance FPDI
$pdf = new Fpdi();
$pdf->AddPage();

// Atur path ke file template PDF
$templatePath = 'template_laporan.pdf';

// Cek apakah file template ada
if (!file_exists($templatePath)) {
    die("Error: File template PDF tidak ditemukan. Pastikan file '" . $templatePath . "' ada di direktori yang benar.");
}

// Impor halaman pertama dari template
$pdf->setSourceFile($templatePath);
$templateId = $pdf->importPage(1);
$size = $pdf->getTemplateSize($templateId);

// Gunakan halaman yang diimpor sebagai latar belakang
$pdf->useTemplate($templateId);

// Sekarang, tambahkan konten laporan ke PDF
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetTextColor(0, 0, 0);

// Tambahkan ringkasan suara
$pdf->SetY(60); // Sesuaikan posisi Y agar tidak menimpa header
$pdf->Cell(0, 10, 'Total suara masuk: ' . $total_votes . ' dari ' . $total_voters . ' pemilih terdaftar.', 0, 1, 'C');

// Ringkasan Status Memilih (teks saja)
$votedCount = (int)$total_votes;
$notVotedCount = max((int)$total_voters - $votedCount, 0);
$votedPercent = ($total_voters > 0) ? round(($votedCount / $total_voters) * 100, 2) : 0;
$notVotedPercent = ($total_voters > 0) ? round(($notVotedCount / $total_voters) * 100, 2) : 0;

// Judul 'Persentase Hasil Suara'
$pdf->SetY(80); // Sesuaikan posisi Y
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Ringkasan Status Memilih', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 8, 'Sudah Memilih: ' . $votedCount . ' orang (' . $votedPercent . '%)', 0, 1, 'C');
$pdf->Cell(0, 8, 'Belum Memilih: ' . $notVotedCount . ' orang (' . $notVotedPercent . '%)', 0, 1, 'C');
$pdf->Ln(6);

// Tabel Hasil Suara
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(80, 7, 'Nama Kandidat', 1);
$pdf->Cell(40, 7, 'Jumlah Suara', 1);
$pdf->Cell(40, 7, 'Persentase', 1, 1);
$pdf->SetFont('Helvetica', '', 10);
foreach ($all_results as $kandidat) {
    $percentage = ($total_akumulasi_suara > 0) ? ($kandidat['jumlah_suara'] / $total_akumulasi_suara) * 100 : 0;
    $pdf->Cell(80, 7, $kandidat['nama_lengkap'], 1);
    $pdf->Cell(40, 7, $kandidat['jumlah_suara'], 1);
    $pdf->Cell(40, 7, round($percentage, 2) . '%', 1, 1);
}
$pdf->Ln(10);

// Tidak menyisipkan pie chart gambar sesuai permintaan

// Bagian detail per nama dihilangkan sesuai permintaan

// ===================================================================
// Tambahan: Ringkasan dan daftar Guru
// ===================================================================

// Hitung jumlah guru dan yang sudah memilih
$total_guru = count($voted_list_guru) + count($not_voted_list_guru);
$total_guru_voted = count($voted_list_guru);
$guru_voted_percent = ($total_guru > 0) ? round(($total_guru_voted / $total_guru) * 100, 2) : 0;

// Tambah halaman baru untuk daftar guru agar layout rapi
$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Daftar Guru - Ringkasan', 0, 1, 'C');
$pdf->Ln(4);
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 8, 'Total guru terdaftar: ' . $total_guru, 0, 1, 'L');
$pdf->Cell(0, 8, 'Guru sudah memilih: ' . $total_guru_voted . ' orang (' . $guru_voted_percent . '%)', 0, 1, 'L');
$pdf->Ln(6);

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(95, 8, 'Guru yang Sudah Memilih', 1);
$pdf->Cell(95, 8, 'Guru yang Belum Memilih', 1, 1);
$pdf->SetFont('Helvetica', '', 11);

// Tentukan jumlah baris maksimum dari kedua list agar penulisan sejajar
$maxRows = max(count($voted_list_guru), count($not_voted_list_guru));

for ($i = 0; $i < $maxRows; $i++) {
    // Kolom kiri: sudah memilih
    if (isset($voted_list_guru[$i])) {
        $leftText = ($i + 1) . '. ' . $voted_list_guru[$i]['nama_lengkap'] . ' (' . $voted_list_guru[$i]['nik'] . ')';
    } else {
        $leftText = '';
    }

    // Kolom kanan: belum memilih
    if (isset($not_voted_list_guru[$i])) {
        $rightText = ($i + 1) . '. ' . $not_voted_list_guru[$i]['nama_lengkap'] . ' (' . $not_voted_list_guru[$i]['nik'] . ')';
    } else {
        $rightText = '';
    }

    $pdf->Cell(95, 7, $leftText, 1);
    $pdf->Cell(95, 7, $rightText, 1, 1);
}


// Outputkan PDF
$pdf->Output("laporan-hasil-voting-" . date("Y-m-d") . ".pdf", "D");

// Tidak ada file sementara untuk dihapus
?>