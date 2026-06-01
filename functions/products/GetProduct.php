<?php

function GetProduct($data, $conn)
{
    $id = $data['id'] ?? '';

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Artikel ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT p.id, p.user_id, p.product_name, p.product_price, p.product_description,
                p.product_availability, p.listing_type, p.bid_deadline, p.created_at,
                u.name AS seller_name, u.username AS seller_username,
                (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_img
         FROM products p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    $base = serveBase('serve_image.php');
    $product['product_img'] = $product['has_img'] ? $base . $product['id'] : null;
    unset($product['has_img']);

    echo json_encode(["success" => true, "data" => $product]);
}
