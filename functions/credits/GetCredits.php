<?php

function GetCredits($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT c.amount FROM credits c
         INNER JOIN users u ON u.credit_id = c.id
         WHERE u.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    $amount = $row ? (int)$row['amount'] : 0;

    echo json_encode(["success" => true, "data" => ["amount" => $amount]]);
}
