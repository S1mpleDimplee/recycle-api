<?php
$allowedOrigins = ['http://localhost:3000', 'http://localhost:5173'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$userId = (int)($_POST['userid'] ?? 0);
if (!$userId || empty($_FILES['file'])) {
    echo json_encode(["success" => false, "message" => "Geen bestand ontvangen"]);
    exit();
}

$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array(mime_content_type($_FILES['file']['tmp_name']), $allowed)) {
    echo json_encode(["success" => false, "message" => "Alleen afbeeldingen zijn toegestaan (jpg, png, webp, gif)"]);
    exit();
}

$uploadDir = __DIR__ . '/../uploads/profiles/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext      = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$filename = $userId . '_' . time() . '.' . $ext;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(["success" => false, "message" => "Uploaden mislukt"]);
    exit();
}

$conn = mysqli_connect("jaylanovanderveen.nl", "jaylanovanderv_recycle", "hawktuah", "jaylanovanderv_recycle");
if ($conn) {
    $imgPath = 'uploads/profiles/' . $filename;
    $stmt    = mysqli_prepare($conn, "UPDATE users SET profile_img = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $imgPath, $userId);
    mysqli_stmt_execute($stmt);
}

echo json_encode([
    "success" => true,
    "url"     => "http://localhost/recycle-api/uploads/profiles/" . $filename,
]);
