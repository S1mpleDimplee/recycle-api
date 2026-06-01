<?php

// Admin – get full data for any user
function GetUserData($data, $conn)
{
    $adminId = $data['adminid'] ?? '';
    $userId  = $data['userid']  ?? '';

    if (empty($adminId) || empty($userId)) {
        echo json_encode(["success" => false, "message" => "adminid en userid zijn verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.username, u.surname, u.email,
                u.adress, u.phonenumber, u.role,
                u.email_verified, u.created_at,
                COALESCE(c.amount, 0) AS credits
         FROM users u
         LEFT JOIN credits c ON c.id = u.credit_id
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
