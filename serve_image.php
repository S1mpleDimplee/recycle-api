<?php
$envPath = __DIR__ . '/.env';
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$key, $val] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($val);
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); exit(); }

$conn = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if (!$conn) { http_response_code(500); exit(); }

$stmt = mysqli_prepare($conn, "SELECT product_img FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $data);
mysqli_stmt_fetch($stmt);

if (empty($data)) { http_response_code(404); exit(); }

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($data) ?: 'image/jpeg';

header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
echo $data;
