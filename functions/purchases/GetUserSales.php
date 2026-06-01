<?php

// Items the user sold – seller history
function GetUserSales($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT
            pur.id, pur.amount_paid, pur.created_at,
            p.id   AS product_id,
            p.product_name, p.product_img, p.product_description,
            buyer.id   AS buyer_id,
            buyer.name AS buyer_name,
            buyer.username AS buyer_username
         FROM purchases pur
         INNER JOIN products p    ON p.id   = pur.product_id
         INNER JOIN users buyer ON buyer.id = pur.buyer_id
         WHERE pur.seller_id = ?
         ORDER BY pur.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $sales = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sales[] = $row;
    }

    echo json_encode(["success" => true, "data" => $sales]);
}
