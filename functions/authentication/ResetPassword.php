<?php

function ResetPassword($data, $conn)
{
    $token    = $data['token']    ?? '';
    $password = $data['password'] ?? '';

    if (empty($token) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Token en wachtwoord zijn verplicht"]);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(["success" => false, "message" => "Wachtwoord moet minimaal 8 tekens bevatten"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Ongeldige of verlopen link. Vraag een nieuwe aan."]);
        return;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $upd    = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'si', $hashed, $user['id']);
    mysqli_stmt_execute($upd);

    echo json_encode(["success" => true, "message" => "Wachtwoord succesvol gewijzigd. Je kunt nu inloggen."]);
}
