<?php

function DisableProduct($data, $conn)
{
    $id          = $data['id']     ?? '';
    $requesterId = $data['userid'] ?? '';
    $action      = $data['action'] ?? 'disable'; // 'disable' or 'enable'

    if (empty($id) || empty($requesterId)) {
        echo json_encode(["success" => false, "message" => "Artikel ID en gebruiker ID zijn verplicht"]);
        return;
    }

    $check = mysqli_prepare($conn, "SELECT user_id FROM products WHERE id = ?");
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }
    if ($product['user_id'] != $requesterId && !isAdmin($requesterId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    if ($action === 'enable') {
        $stmt = mysqli_prepare($conn, "UPDATE products SET product_availability = 'available' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(["success" => true, "message" => "Artikel weer actief"]);
        return;
    }

    // Cancel all pending bids before disabling
    $cancel = mysqli_prepare($conn,
        "UPDATE bids SET status = 'seller_deleted' WHERE product_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($cancel, 'i', $id);
    mysqli_stmt_execute($cancel);

    $stmt = mysqli_prepare($conn, "UPDATE products SET product_availability = 'disabled' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    echo json_encode(["success" => true, "message" => "Artikel gedeactiveerd en biedingen geannuleerd"]);
}
