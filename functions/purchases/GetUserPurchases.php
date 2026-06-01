<?php

// Items the user bought – shown on /users/purchases
function GetUserPurchases($data, $conn)
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
            p.product_name, p.product_description,
            (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_img,
            seller.id   AS seller_id,
            seller.name AS seller_name,
            seller.username AS seller_username
         FROM purchases pur
         INNER JOIN products p    ON p.id    = pur.product_id
         INNER JOIN users seller ON seller.id = pur.seller_id
         WHERE pur.buyer_id = ?
         ORDER BY pur.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/phpopdrachten/derde_jaar/recycle-api/serve_image.php?id=';
    $purchases = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['product_img'] = $row['has_img'] ? $base . $row['product_id'] : null;
        unset($row['has_img']);
        $purchases[] = $row;
    }

    echo json_encode(["success" => true, "data" => $purchases]);
}
