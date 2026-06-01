<?php

// Admin – update any user's data including role
function UpdateUserData($data, $conn)
{
    $adminId     = $data['adminid']     ?? '';
    $userId      = $data['userid']      ?? '';
    $name        = $data['name']        ?? '';
    $username    = $data['username']    ?? '';
    $surname     = $data['surname']     ?? '';
    $email       = $data['email']       ?? '';
    $adress      = $data['adress']      ?? '';
    $phonenumber = $data['phonenumber'] ?? '';
    $role        = $data['role']        ?? 'user';
    $verified    = isset($data['email_verified']) ? (int)$data['email_verified'] : null;

    if (empty($adminId) || empty($userId)) {
        echo json_encode(["success" => false, "message" => "adminid en userid zijn verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    if ($verified !== null) {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, username=?, surname=?, email=?, adress=?, phonenumber=?, role=?, email_verified=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssii', $name, $username, $surname, $email, $adress, $phonenumber, $role, $verified, $userId);
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, username=?, surname=?, email=?, adress=?, phonenumber=?, role=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssi', $name, $username, $surname, $email, $adress, $phonenumber, $role, $userId);
    }

    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij bijwerken: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Gebruiker succesvol bijgewerkt"]);
}
