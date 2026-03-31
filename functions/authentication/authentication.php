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

function addUser($data, $conn)
{
    $email = $data['email'] ?? null;
    $phonenumber = $data['phonenumber'] ?? null;
    $password = $data['password'] ?? null;
    $name = $data['name'] ?? $data['firstname'] ?? $email ?? '';

    if (empty($email) || empty($password) || empty($phonenumber)) {
        echo json_encode([
            "success" => false,
            "message" => "Alle verplichte velden moeten ingevuld zijn"
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

    // if (!isPasswordStrong($password, $message)) {
    //     echo json_encode([
    //         "success" => false,
    //         "message" => $message
    //     ]);
    //     return;
    // }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (email, phonenumber, password, name, created_at) 
            VALUES (?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $email, $phonenumber, $hashedPassword, $name);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij aanmaken gebruiker: " . mysqli_last_error($conn)
        ]);
        return;
    }

    $userid = mysqli_insert_id($conn);

    echo json_encode([
        "success" => true,
        "message" => "Account is succesvol aangemaakt",
        "data" => [
            "userid" => $userid,
        ]
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
            "message" => "Database fout: " . mysqli_last_error($conn)
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
                "userid" => $user['id'],
                "email" => $user['email'],
                "role" => $user['role'] ?? null,
                "phonenumber" => $user['phonenumber']
            ],
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email bestaat niet of wachtwoord is onjuist, probeer het opnieuw"
        ]);
    }
}
?>  