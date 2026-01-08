<?php
// Migration script to add program_kerja column to kandidat table
require_once 'config/koneksi.php';

$sql = "ALTER TABLE kandidat ADD COLUMN program_kerja TEXT DEFAULT NULL AFTER misi";

if ($koneksi->query($sql) === TRUE) {
    echo "Kolom program_kerja berhasil ditambahkan ke tabel kandidat.";
} else {
    echo "Error menambahkan kolom: " . $koneksi->error;
}

$koneksi->close();
?>