<?php

function GetUserProducts($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT id, user_id, product_name, product_price, product_description,
                product_availability, listing_type, bid_deadline, created_at,
                (product_img IS NOT NULL AND LENGTH(product_img) > 0) AS has_img
         FROM products WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $base = serveBase('serve_image.php');
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['product_img'] = $row['has_img'] ? $base . $row['id'] : null;
        unset($row['has_img']);
        $products[] = $row;
    }

    echo json_encode(["success" => true, "data" => $products]);
}
