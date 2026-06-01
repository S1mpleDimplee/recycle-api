<?php

function GetProduct($data, $conn)
{
    $id = $data['id'] ?? '';

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Artikel ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT p.*, u.name AS seller_name, u.username AS seller_username
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

    echo json_encode(["success" => true, "data" => $product]);
}
