<?php

function GetAllProducts($conn)
{
    $sql = "SELECT p.*, u.name AS seller_name, u.username AS seller_username
            FROM products p
            LEFT JOIN users u ON u.id = p.user_id
            ORDER BY p.id DESC";
    $result = mysqli_query($conn, $sql);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode(["success" => true, "data" => $products]);
}
