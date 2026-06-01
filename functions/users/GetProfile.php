<?php

function GetProfile($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.username, u.surname, u.email, u.adress, u.phonenumber, u.role, c.amount AS credits
         FROM user u
         LEFT JOIN credit c ON c.id = u.credit_id
         WHERE u.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Gebruiker niet gevonden"]);
        return;
    }

    echo json_encode(["success" => true, "data" => $user]);
}
