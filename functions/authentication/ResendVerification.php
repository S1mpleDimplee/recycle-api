<?php

function ResendVerification($data, $conn)
{
    $email = trim($data['email'] ?? '');

    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "E-mailadres is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, name, email_verified FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Geen account gevonden voor dit e-mailadres."]);
        return;
    }

    if ($user['email_verified'] == 1) {
        echo json_encode(["success" => false, "message" => "Dit account is al geverifieerd."]);
        return;
    }

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    SendVerificationEmail([
        'email'            => $email,
        'name'             => $user['name'],
        'verificationcode' => $code,
    ], $conn);
}
