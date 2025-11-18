<?php
require_once 'config/koneksi.php';

// Data guru contoh
$guru_data = [
    [
        'nama_lengkap' => 'Dr. Siti Nurhaliza, M.Pd',
        'nik' => '1234567890123456',
        'password' => 'guru123',
        'jabatan' => 'Kepala Sekolah'
    ],
    [
        'nama_lengkap' => 'Budi Santoso, S.Pd',
        'nik' => '2345678901234567',
        'password' => 'guru456',
        'jabatan' => 'Wakil Kepala Sekolah'
    ],
    [
        'nama_lengkap' => 'Sari Indah, S.Pd',
        'nik' => '3456789012345678',
        'password' => 'guru789',
        'jabatan' => 'Guru Matematika'
    ],
    [
        'nama_lengkap' => 'Ahmad Fauzi, S.Pd',
        'nik' => '4567890123456789',
        'password' => 'guru101',
        'jabatan' => 'Guru Bahasa Indonesia'
    ],
    [
        'nama_lengkap' => 'Dewi Kartika, S.Pd',
        'nik' => '5678901234567890',
        'password' => 'guru202',
        'jabatan' => 'Guru Bahasa Inggris'
    ]
];

try {
    foreach ($guru_data as $guru) {
        // Cek apakah NIK sudah ada
        $stmt_check = $koneksi->prepare("SELECT id FROM guru WHERE nik = ?");
        $stmt_check->bind_param("s", $guru['nik']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows == 0) {
            // Tambahkan guru baru
            $stmt = $koneksi->prepare("INSERT INTO guru (nama_lengkap, nik, password, jabatan, status_memilih) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $guru['nama_lengkap'], $guru['nik'], $guru['password'], $guru['jabatan']);
            
            if ($stmt->execute()) {
                echo "Guru " . $guru['nama_lengkap'] . " berhasil ditambahkan.<br>";
            } else {
                echo "Gagal menambahkan guru " . $guru['nama_lengkap'] . ".<br>";
            }
            
            $stmt->close();
        } else {
            echo "Guru dengan NIK " . $guru['nik'] . " sudah ada.<br>";
        }
        
        $stmt_check->close();
    }
    
    echo "<br>Proses selesai!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$koneksi->close();
?>
