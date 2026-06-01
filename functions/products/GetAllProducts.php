<?php

function GetAllProducts($conn)
{
    $sql = "SELECT p.id, p.user_id, p.product_name, p.product_price, p.product_description,
                   p.product_availability, p.listing_type, p.bid_deadline, p.created_at,
                   u.name AS seller_name, u.username AS seller_username,
                   (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_img
            FROM products p
            LEFT JOIN users u ON u.id = p.user_id
            ORDER BY p.id DESC";
    $result = mysqli_query($conn, $sql);

    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/phpopdrachten/derde_jaar/recycle-api/serve_image.php?id=';
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['product_img'] = $row['has_img'] ? $base . $row['id'] : null;
        unset($row['has_img']);
        $products[] = $row;
    }

    echo json_encode(["success" => true, "data" => $products]);
}
