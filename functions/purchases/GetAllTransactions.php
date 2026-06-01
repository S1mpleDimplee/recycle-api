<?php

// Admin – full transaction log
function GetAllTransactions($data, $conn)
{
    $adminId = $data['adminid'] ?? '';

    if (empty($adminId)) {
        echo json_encode(["success" => false, "message" => "Admin ID is verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    $result = mysqli_query($conn,
        "SELECT
            pur.id, pur.amount_paid, pur.created_at,
            p.id   AS product_id, p.product_name,
            buyer.id   AS buyer_id,  buyer.name  AS buyer_name,
            seller.id  AS seller_id, seller.name AS seller_name
         FROM purchases pur
         INNER JOIN products p    ON p.id    = pur.product_id
         INNER JOIN users buyer  ON buyer.id  = pur.buyer_id
         INNER JOIN users seller ON seller.id = pur.seller_id
         ORDER BY pur.created_at DESC");

    $transactions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }

    echo json_encode(["success" => true, "data" => $transactions]);
}
