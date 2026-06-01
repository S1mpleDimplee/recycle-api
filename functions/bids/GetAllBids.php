<?php

function GetAllBids($data, $conn)
{
    $adminId = $data['userid'] ?? '';
    requireAdmin($adminId, $conn);

    $stmt = mysqli_prepare($conn, "
        SELECT b.id, b.product_id, b.bidder_id, b.amount, b.status, b.created_at,
               p.product_name,
               u.name AS bidder_name, u.email AS bidder_email
        FROM bids b
        JOIN products p ON b.product_id = p.id
        JOIN users u ON b.bidder_id = u.id
        ORDER BY b.created_at DESC
    ");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bids = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode(["success" => true, "data" => $bids]);
}
