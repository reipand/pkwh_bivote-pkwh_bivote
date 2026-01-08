<?php
// Test endpoint untuk memeriksa konfigurasi upload PHP
header("Content-Type: application/json; charset=UTF-8");

$upload_max_filesize = ini_get('upload_max_filesize');
$post_max_size = ini_get('post_max_size');
$max_execution_time = ini_get('max_execution_time');
$memory_limit = ini_get('memory_limit');

echo json_encode([
    "upload_max_filesize" => $upload_max_filesize,
    "post_max_size" => $post_max_size,
    "max_execution_time" => $max_execution_time,
    "memory_limit" => $memory_limit,
    "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    "php_version" => phpversion()
]);
?>