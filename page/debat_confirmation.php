<?php
session_start();

$status = $_SESSION['debat_status'] ?? 'failed';
$user_name = $_SESSION['user_nama'] ?? 'Pengguna';

$message_title = '';
$message_body = '';
$icon_svg = '';
$redirect_url = 'kepuasan_debat.php';

if ($status === 'success') {
    $message_title = 'Terima kasih!';
    $message_body = 'Penilaian Anda berhasil dicatat.';
    $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-16 h-16 text-green-500 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
} elseif ($status === 'already_voted') {
    $message_title = 'Anda Sudah Memberikan Penilaian!';
    $message_body = 'Anda telah memberikan penilaian untuk sesi ini.';
    $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-16 h-16 text-yellow-500 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.731 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';
} else {
    $message_title = 'Terjadi Kesalahan!';
    $message_body = 'Penilaian Anda tidak dapat dicatat. Silakan coba kembali.';
    $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-16 h-16 text-red-500 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
}

unset($_SESSION['debat_status']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Penilaian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body class="bg-gray-100 font-sans flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-lg max-w-md w-full text-center">
        <div class="flex flex-col items-center justify-center mb-6">
            <?php echo $icon_svg; ?>
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($message_title); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($message_body); ?></p>
        </div>
        <a href="<?php echo $redirect_url; ?>" class="bg-indigo-600 text-white font-medium py-3 px-8 rounded-full hover:bg-indigo-700 transition-colors">Lanjut</a>
    </div>
</body>
</html>