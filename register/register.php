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
    $firstName = $data['firstname'] ?? null;
    $lastName = $data['lastname'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $phonenumber = $data['phonenumber'] ?? null;
    $streetname = $data['streetname'] ?? null;
    $housenumber = $data['housenumber'] ?? null;
    $postalcode = $data['postcode'] ?? null;
    $city = $data['city'] ?? null;
    $country = $data['country'] ?? null;

    // First check if email is already in use
    if (isEmailRegistered($email, $conn)) {
        echo json_encode([
            "success" => false,
            "message" => "Dit email adres is al geregistreerd, probeer een andere email adres"
        ]);
        return;
    }

    // Password strength check (uncomment if needed)
    // if (!isPasswordStrong($password, $message)) {
    //     echo json_encode([
    //         "success" => false,
    //         "message" => $message
    //     ]);
    //     return;
    // }

    // Check required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode([
            "success" => false,
            "message" => "Alle verplichte velden moeten ingevuld zijn"
        ]);
        return;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (firstname, lastname, email, phonenumber, created_at) VALUES ('$firstName', '$lastName', '$email', '$phonenumber', NOW()) ON DUPLICATE KEY UPDATE email=VALUES(email)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij aanmaken gebruiker: " . mysqli_error($conn)
        ]);
        return;
    }

    // Get the auto-generated userid (this will be an integer like 11, 12, 13...)
    $userId = mysqli_insert_id($conn);

    $displayUserId = 'U-' . str_pad($userId, 5, '0', STR_PAD_LEFT);

    $updateUserIDSQL = "UPDATE user SET userid='$displayUserId' WHERE id=$userId";
    mysqli_query($conn, $updateUserIDSQL);
    // Insert password using the integer userid
    $passwordSql = "INSERT INTO userpassword (userid, password) VALUES ('$displayUserId', '$hashedPassword')";
    $passwordResult = mysqli_query($conn, $passwordSql);

    if (!$passwordResult) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij opslaan wachtwoord: " . mysqli_error($conn)
        ]);
        return;
    }

    // Insert address if provided
    if (!empty($streetname) || !empty($city)) {
        $housenumberValue = !empty($housenumber) ? $housenumber : 0;
        $addUserAddressSql = "INSERT INTO useradress (userid, adress, streetname, city, country, housenumber) VALUES ('$displayUserId', '$postalcode', '$streetname', '$city', '$country', $housenumberValue)";

        mysqli_query($conn, $addUserAddressSql);
    }

    echo json_encode([
        "success" => true,
        "message" => "Account is succesvol aangemaakt",
        "data" => [
            "userid" => $displayUserId,
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

    // Fixed table name: userpassword (not userpasswords)
    $sql = "SELECT u.*, p.password FROM user u JOIN userpassword p ON u.userid = p.userid WHERE u.email='$email'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Database fout: " . mysqli_error($conn)
        ]);
        return;
    }

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $displayUserId = 'U-' . str_pad($user['userid'], 5, '0', STR_PAD_LEFT);

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "data" => [
                "userid" => $user['userid'],
                "displayUserId" => $displayUserId,
                "firstName" => $user['firstname'],
                "lastName" => $user['lastname'],
                "email" => $user['email'],
                "role" => $user['role'],
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
