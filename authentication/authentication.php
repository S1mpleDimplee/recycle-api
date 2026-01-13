<?php

include_once '../functions/isEmailRegistered.php';

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

    if (empty($email) || empty($password)) {
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

    $sql = "INSERT INTO users (email, phonenumber, password, created_at) 
            VALUES ($email, $phonenumber, $hashedPassword, NOW()) ";

    $result = mysqli_query_params($conn, $sql, array($email, $phonenumber, $hashedPassword));

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij aanmaken gebruiker: " . mysqli_last_error($conn)
        ]);
        return;
    }

    $row = mysqli_fetch_assoc($result);
    $userid = $row['userid'];

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

    $sql = "SELECT * FROM users WHERE email = $email";
    $result = mysqli_query_params($conn, $sql, array($email));

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Database fout: " . mysqli_last_error($conn)
        ]);
        return;
    }

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "data" => [
                "userid" => $user['userid'],
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