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
                p.product_availability,
                (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_img,
                u.name AS seller_name
         FROM bids b
         INNER JOIN products p  ON p.id = b.product_id
         INNER JOIN users u ON u.id = p.user_id
         WHERE b.bidder_id = ?
         ORDER BY b.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $base = serveBase('serve_image.php');
    $bids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['product_img'] = $row['has_img'] ? $base . $row['product_id'] : null;
        unset($row['has_img']);
        $bids[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bids]);
}
