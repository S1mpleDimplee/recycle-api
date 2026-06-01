<?php

function GetPublicBids($data, $conn)
{
    $productId = $data['product_id'] ?? '';

    if (empty($productId)) {
        echo json_encode(["success" => false, "message" => "product_id is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT b.bidder_id, b.amount, b.created_at,
                u.name AS bidder_name, u.username AS bidder_username
         FROM bids b
         INNER JOIN users u ON u.id = b.bidder_id
         WHERE b.product_id = ? AND b.status = 'pending'
         ORDER BY b.amount DESC, b.created_at ASC");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $bids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bids[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bids]);
}
