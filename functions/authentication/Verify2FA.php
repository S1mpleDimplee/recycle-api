<?php

function Verify2FA($data, $conn)
{
    $userId = (int)($data['userid'] ?? 0);
    $code   = trim($data['code']   ?? '');

    if (!$userId || empty($code)) {
        echo json_encode(["success" => false, "message" => "Gebruiker en code zijn verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT id, name, username, email, role, phonenumber, two_fa_code, two_fa_expires
         FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Gebruiker niet gevonden"]);
        return;
    }

    if ($user['two_fa_code'] !== $code || strtotime($user['two_fa_expires']) < time()) {
        echo json_encode(["success" => false, "message" => "Ongeldige of verlopen code"]);
        return;
    }

    $clear = mysqli_prepare($conn, "UPDATE users SET two_fa_code = NULL, two_fa_expires = NULL WHERE id = ?");
    mysqli_stmt_bind_param($clear, 'i', $userId);
    mysqli_stmt_execute($clear);

    echo json_encode([
        "success" => true,
        "message" => "Ingelogd",
        "data"    => [
            "userid"      => $user['id'],
            "name"        => $user['name'],
            "username"    => $user['username'],
            "email"       => $user['email'],
            "role"        => $user['role'],
            "phonenumber" => $user['phonenumber'],
        ],
    ]);
}
