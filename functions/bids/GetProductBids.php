<?php

// Bids placed ON a product – only visible to the product owner
function GetProductBids($data, $conn)
{
    $userId    = $data['userid']     ?? '';
    $productId = $data['product_id'] ?? '';

    if (empty($userId) || empty($productId)) {
        echo json_encode(["success" => false, "message" => "userid en product_id zijn verplicht"]);
        return;
    }

    // Verify the requester owns the product (or is admin)
    $ownerStmt = mysqli_prepare($conn, "SELECT user_id FROM products p WHERE id = ?");
    mysqli_stmt_bind_param($ownerStmt, 'i', $productId);
    mysqli_stmt_execute($ownerStmt);
    $ownerResult = mysqli_stmt_get_result($ownerStmt);
    $product = mysqli_fetch_assoc($ownerResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }
    if ($product['user_id'] != $userId && !isAdmin($userId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT b.id, b.amount, b.status, b.created_at,
                u.id AS bidder_id, u.name AS bidder_name, u.username AS bidder_username
         FROM bids b
         INNER JOIN users u ON u.id = b.bidder_id
         WHERE b.product_id = ?
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
