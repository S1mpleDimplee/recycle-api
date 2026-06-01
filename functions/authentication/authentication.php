<?php

include_once '../functions/mail/isEmailRegistered.php';

function isPasswordStrong($password, &$message)
{
    if (strlen($password) < 8) {
        $message = "Wachtwoord moet minimaal 8 tekens bevatten";
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $message = "Wachtwoord moet minimaal één hoofdletter bevatten";
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        $message = "Wachtwoord moet minimaal één cijfer bevatten";
        return false;
    }
    if (!preg_match('/[\W]/', $password)) {
        $message = "Wachtwoord moet minimaal één speciaal teken bevatten zoals !, @, #, $, -, etc.";
        return false;
    }
    return true;
}

function registerUser($data, $conn)
{
    $email       = $data['email']       ?? null;
    $phonenumber = $data['phonenumber'] ?? null;
    $password    = $data['password']    ?? null;
    $name        = $data['name']        ?? $data['firstname'] ?? '';
    $username    = $data['username']    ?? '';
    $surname     = $data['surname']     ?? '';
    $adress      = $data['adress']      ?? '';

    if (empty($email) || empty($password) || empty($phonenumber)) {
        echo json_encode([
            "success" => false,
            "message" => "Naam, email, wachtwoord en telefoonnummer zijn verplicht"
        ]);
        return;
    }

    if (isEmailRegistered($email, $conn)) {
        echo json_encode([
            "success" => false,
            "message" => "Dit email adres is al geregistreerd, probeer een andere email adres"
        ]);
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Create credit record first (50 starting Recy's) so we have the id for the user insert
    $creditStmt = mysqli_prepare($conn, "INSERT INTO credits (amount) VALUES (50)");
    mysqli_stmt_execute($creditStmt);
    $creditId = mysqli_insert_id($conn);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (email, phonenumber, password, name, username, surname, adress, role, credit_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'user', ?)");
    mysqli_stmt_bind_param($stmt, 'sssssssi',
        $email, $phonenumber, $hashedPassword, $name, $username, $surname, $adress, $creditId);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij aanmaken gebruiker: " . mysqli_error($conn)
        ]);
        return;
    }

    $userid = mysqli_insert_id($conn);

    $verificationcode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    SendVerificationEmail([
        'email'            => $email,
        'name'             => $name,
        'verificationcode' => $verificationcode,
    ], $conn);
}

function checkLogin($data, $conn)
{
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (empty($email) || empty($password)) {
        echo json_encode([
            "success" => false,
            "message" => "Email en wachtwoord zijn verplicht"
        ]);
        return;
    }

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Database fout: " . mysqli_error($conn)
        ]);
        return;
    }

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password']) && $user['email_verified'] == 0) {
        echo json_encode([
            "success"       => false,
            "not_verified"  => true,
            "email"         => $user['email'],
            "message"       => "Je e-mailadres is nog niet bevestigd. Controleer je inbox voor de verificatiecode.",
        ]);
        return;
    }

    if ($user && password_verify($password, $user['password']) && $user['email_verified'] == 1) {
        if ($user['role'] === 'admin') {
            $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $upd = mysqli_prepare($conn, "UPDATE users SET two_fa_code = ?, two_fa_expires = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, 'ssi', $code, $expires, $user['id']);
            mysqli_stmt_execute($upd);
            try {
                require_once __DIR__ . '/../mail/mailer.php';
                $mail = createMailer();
                $body = emailTemplate('Inlogcode - Tradr Admin', '
                    <p style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1a1a1a;">Hallo ' . htmlspecialchars($user['name']) . ',</p>
                    <p style="margin:0 0 24px;font-size:15px;color:#555;line-height:1.6;">
                        Gebruik de onderstaande code om in te loggen op het beheerderspaneel.
                    </p>
                    <div style="background:#e8f5e9;border:2px solid #2e7d32;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px;">
                        <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:#2e7d32;text-transform:uppercase;letter-spacing:1px;">Inlogcode</p>
                        <p style="margin:0;font-size:42px;font-weight:800;color:#2e7d32;letter-spacing:10px;">' . $code . '</p>
                    </div>
                    <p style="margin:0;font-size:13px;color:#888;text-align:center;">
                        Deze code is <strong>10 minuten</strong> geldig. Deel hem met niemand.
                    </p>
                ');
                $mail->addAddress($user['email'], $user['name']);
                $mail->Subject = 'Inlogcode - Tradr Admin';
                $mail->isHTML(true);
                $mail->Body    = $body;
                $mail->AltBody = "Hallo " . $user['name'] . ",\n\nJe inlogcode is: " . $code . "\n\nDeze code is 10 minuten geldig.";
                $mail->send();
            } catch (Exception $e) {}
            echo json_encode([
                "success"        => true,
                "requires_2fa"   => true,
                "pending_userid" => $user['id'],
            ]);
        } else {
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "data"    => [
                    "userid"      => $user['id'],
                    "name"        => $user['name'],
                    "username"    => $user['username'],
                    "email"       => $user['email'],
                    "role"        => $user['role'] ?? 'user',
                    "phonenumber" => $user['phonenumber'],
                ],
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email bestaat niet of wachtwoord is onjuist, probeer het opnieuw"
        ]);
    }
}
