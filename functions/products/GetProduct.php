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
                u.name AS seller_name, u.username AS seller_username
         FROM products p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    $images              = getProductImages($product['id'], $conn);
    $product['images']   = $images;
    $product['product_img'] = $images[0] ?? null; // backward compat

    echo json_encode(["success" => true, "data" => $product]);
}
