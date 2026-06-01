<?php

// All bids received across ALL products owned by a user
function GetAllProductBids($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT b.id, b.amount, b.status, b.created_at,
                p.id AS product_id, p.product_name,
                (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_img,
                u.id AS bidder_id, u.name AS bidder_name, u.username AS bidder_username
         FROM bids b
         INNER JOIN products p ON p.id = b.product_id
         INNER JOIN users u ON u.id = b.bidder_id
         WHERE p.user_id = ?
         ORDER BY b.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/phpopdrachten/derde_jaar/recycle-api/serve_image.php?id=';
    $bids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['product_img'] = $row['has_img'] ? $base . $row['product_id'] : null;
        unset($row['has_img']);
        $bids[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bids]);
}
