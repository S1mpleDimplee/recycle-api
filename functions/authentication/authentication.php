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
    $creditStmt = mysqli_prepare($conn, "INSERT INTO credit (amount) VALUES (50)");
    mysqli_stmt_execute($creditStmt);
    $creditId = mysqli_insert_id($conn);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO user (email, phonenumber, password, name, username, surname, adress, role, credit_id)
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

    echo json_encode([
        "success" => true,
        "message" => "Account is succesvol aangemaakt",
        "data"    => ["userid" => $userid]
    ]);
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

    $sql = "SELECT * FROM user WHERE email = ?";
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

    if ($user && password_verify($password, $user['password']) && $user['email_verified'] == 1) {
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "data" => [
                "userid"      => $user['id'],
                "name"        => $user['name'],
                "username"    => $user['username'],
                "email"       => $user['email'],
                "role"        => $user['role'] ?? 'user',
                "phonenumber" => $user['phonenumber'],
            ],
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email bestaat niet of wachtwoord is onjuist, probeer het opnieuw"
        ]);
    }
}
