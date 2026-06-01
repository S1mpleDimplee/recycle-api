<?php

function UpdateProfile($data, $conn)
{
    $userId      = $data['userid']      ?? '';
    $name        = $data['name']        ?? '';
    $username    = $data['username']    ?? '';
    $surname     = $data['surname']     ?? '';
    $email       = $data['email']       ?? '';
    $adress      = $data['adress']      ?? '';
    $phonenumber = $data['phonenumber'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE user SET name=?, username=?, surname=?, email=?, adress=?, phonenumber=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $username, $surname, $email, $adress, $phonenumber, $userId);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij bijwerken: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Profiel succesvol bijgewerkt"]);
}
