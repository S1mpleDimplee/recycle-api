<?php

// Bids the logged-in user has placed (for /users/bids page)
function GetUserBids($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT b.id, b.amount, b.status, b.created_at,
                p.id AS product_id, p.product_name, p.product_price,
                p.product_img, p.product_availability,
                u.name AS seller_name
         FROM bids b
         INNER JOIN products p  ON p.id = b.product_id
         INNER JOIN users u ON u.id = p.user_id
         WHERE b.bidder_id = ?
         ORDER BY b.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $bids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bids[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bids]);
}
