# Implementasi Fitur Guru untuk Sistem Voting BiVOTE

## Ringkasan Implementasi

Saya telah berhasil mengimplementasikan fitur guru untuk sistem voting BiVOTE dengan integrasi penuh ke dalam sistem yang sudah ada. Berikut adalah detail implementasi:

## 1. Update Halaman Login (login.php)

### Perubahan yang dilakukan:
- **Toggle Login**: Menambahkan toggle antara "Login Siswa" dan "Login Guru"
- **Form Terpisah**: Membuat form terpisah untuk siswa dan guru
- **Validasi Input**: 
  - Siswa: NIS + Tanggal Lahir (format DD/MM/YY)
  - Guru: NIK + Password
- **JavaScript**: Update login.js untuk mendukung kedua form

### Fitur:
- Interface yang user-friendly dengan toggle tab
- Validasi input yang sesuai untuk masing-masing tipe user
- Feedback error yang jelas

## 2. API Login Guru (api/login_guru.php)

### Fitur:
- **Autentikasi**: Login menggunakan NIK dan password
- **Session Management**: Menyimpan data guru di session dengan identifier khusus
- **Status Voting**: Mengecek status voting guru
- **Redirect Logic**: Redirect ke dashboard atau konfirmasi sesuai status

### Session Variables untuk Guru:
```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nik'] = $user['nik'];
$_SESSION['user_nama'] = $user['nama_lengkap'];
$_SESSION['user_jabatan'] = $user['jabatan'];
$_SESSION['is_logged_in'] = true;
$_SESSION['user_type'] = 'guru'; // Identifier khusus
```

## 3. Update API Voting

### vote_handler.php:
- **Multi-table Support**: Mendukung voting dari tabel `pemilih` dan `guru`
- **User Type Detection**: Menggunakan `$_SESSION['user_type']` untuk menentukan tabel
- **Transaction Safety**: Transaksi database yang aman
- **Status Update**: Update status voting dan pilihan kandidat

### submit_vote.php:
- **Unified API**: API yang sama untuk siswa dan guru
- **Type-based Logic**: Logika berbeda berdasarkan tipe user
- **Data Integrity**: Memastikan konsistensi data

## 4. Update Dashboard (dashboard.php)

### Perubahan:
- **User Type Detection**: Mendeteksi tipe user (siswa/guru)
- **Customized Display**: 
  - Siswa: "Pilih Kandidat Jagoan mu!"
  - Guru: "Pilih Kandidat Ketua OSIS yang Anda Dukung!" + Jabatan
- **Status Check**: Mengecek status voting dari tabel yang sesuai

## 5. Update Halaman Visi (visi.php)

### Perubahan:
- **Multi-user Support**: Mendukung voting dari siswa dan guru
- **Status Check**: Mengecek status voting dari tabel yang sesuai
- **Consistent Experience**: Pengalaman yang sama untuk semua user

## 6. Update Halaman Konfirmasi (vote_confirmation.php)

### Perubahan:
- **User Type Support**: Mendukung konfirmasi untuk guru
- **Consistent Messaging**: Pesan yang konsisten untuk semua user

## 7. Update API Hasil (api/get_results.php)

### Perubahan:
- **Multi-table Statistics**: Statistik terpisah untuk siswa dan guru
- **Combined Results**: Hasil gabungan dari semua pemilih
- **Detailed Breakdown**: Breakdown statistik yang detail

### Data yang dikembalikan:
```json
{
  "total_voters_siswa": 8,
  "total_voters_guru": 5,
  "total_voters": 13,
  "total_votes_siswa": 3,
  "total_votes_guru": 2,
  "total_votes": 5
}
```

## 8. Update Laporan Admin (admin/reports.php)

### Perubahan:
- **Multi-source Data**: Data dari tabel `pemilih` dan `guru`
- **Separate Statistics**: Statistik terpisah untuk siswa dan guru
- **Combined View**: Tampilan gabungan dengan breakdown detail
- **Voter Lists**: Daftar pemilih yang sudah/belum memilih dari kedua tabel

## 9. Halaman Kelola Guru (admin/manage_guru.php)

### Fitur:
- **CRUD Operations**: Create, Read, Update, Delete guru
- **Data Management**: Kelola data guru lengkap
- **Status Tracking**: Tracking status voting guru
- **User Interface**: Interface yang konsisten dengan admin panel

## 10. API Kelola Guru (admin/add_guru.php)

### Fitur:
- **Data Validation**: Validasi input yang ketat
- **Duplicate Check**: Cek NIK duplikat
- **Error Handling**: Penanganan error yang baik
- **Success Feedback**: Feedback sukses/gagal

## 11. Update Sidebar Admin

### Perubahan:
- **New Menu**: Menambahkan menu "Kelola Guru"
- **Icon**: Icon yang sesuai untuk guru
- **Navigation**: Navigasi yang konsisten

## 12. Database Integration

### Tabel Guru:
```sql
CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_memilih` tinyint(1) DEFAULT 0,
  `id_kandidat_dipilih` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`)
);
```

## Cara Penggunaan

### Untuk Guru:
1. Buka halaman login
2. Pilih tab "Login Guru"
3. Masukkan NIK dan password
4. Klik "LOGIN GURU"
5. Pilih kandidat dan lakukan voting

### Untuk Admin:
1. Login sebagai admin
2. Akses menu "Kelola Guru"
3. Tambah/edit/hapus data guru
4. Lihat laporan dengan statistik terpisah

## Keamanan

### Implementasi Keamanan:
- **Prepared Statements**: Semua query menggunakan prepared statements
- **Input Validation**: Validasi input yang ketat
- **Session Management**: Session yang aman dengan identifier khusus
- **Transaction Safety**: Transaksi database yang aman

## Testing

### Data Guru Contoh:
```sql
INSERT INTO guru (nama_lengkap, nik, password, jabatan, status_memilih) VALUES
('Dr. Siti Nurhaliza, M.Pd', '1234567890123456', 'guru123', 'Kepala Sekolah', 0),
('Budi Santoso, S.Pd', '2345678901234567', 'guru456', 'Wakil Kepala Sekolah', 0),
('Sari Indah, S.Pd', '3456789012345678', 'guru789', 'Guru Matematika', 0),
('Ahmad Fauzi, S.Pd', '4567890123456789', 'guru101', 'Guru Bahasa Indonesia', 0),
('Dewi Kartika, S.Pd', '5678901234567890', 'guru202', 'Guru Bahasa Inggris', 0);
```

## Kesimpulan

Implementasi fitur guru telah selesai dengan:
- ✅ Login terintegrasi di halaman yang sama
- ✅ API voting yang mendukung guru
- ✅ Dashboard yang menampilkan informasi sesuai user
- ✅ Laporan admin dengan statistik terpisah
- ✅ Kelola guru di admin panel
- ✅ Keamanan dan validasi yang baik
- ✅ User experience yang konsisten

Sistem sekarang mendukung voting dari siswa dan guru dengan interface yang terintegrasi dan fungsionalitas yang lengkap.
