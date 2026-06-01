<?php

function GetAllUsers($data, $conn)
{
    $adminId = $data['adminid'] ?? '';

    if (empty($adminId)) {
        echo json_encode(["success" => false, "message" => "Admin ID is verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    $result = mysqli_query($conn,
        "SELECT u.id, u.name, u.username, u.surname, u.email, u.role, u.phonenumber,
                c.amount AS credits,
                (u.profile_img IS NOT NULL AND LENGTH(u.profile_img) > 0) AS has_img
         FROM users u
         LEFT JOIN credits c ON c.id = u.credit_id
         ORDER BY u.id DESC");

    $base = serveBase('serve_profile.php');
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['profile_img'] = $row['has_img'] ? $base . $row['id'] : null;
        unset($row['has_img']);
        $users[] = $row;
    }

    echo json_encode(["success" => true, "data" => $users]);
}
