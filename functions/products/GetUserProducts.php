<?php

function GetUserProducts($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM p WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode(["success" => true, "data" => $products]);
}
