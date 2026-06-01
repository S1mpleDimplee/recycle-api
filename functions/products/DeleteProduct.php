<?php

function DeleteProduct($data, $conn)
{
    $id          = $data['id']     ?? '';
    $requesterId = $data['userid'] ?? '';

    if (empty($id) || empty($requesterId)) {
        echo json_encode(["success" => false, "message" => "Artikel ID en gebruiker ID zijn verplicht"]);
        return;
    }

    // Fetch product owner + deadline
    $check = mysqli_prepare($conn, "SELECT user_id, bid_deadline FROM products p WHERE id = ?");
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $checkResult = mysqli_stmt_get_result($check);
    $product = mysqli_fetch_assoc($checkResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    // Allow owner or admin
    if ($product['user_id'] != $requesterId && !isAdmin($requesterId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    // Block deletion while an active auction is running
    $deadline = $product['bid_deadline'] ?? null;
    if (!empty($deadline) && strtotime($deadline) > time()) {
        $activeBids = mysqli_prepare($conn,
            "SELECT COUNT(*) AS cnt FROM bids WHERE product_id = ? AND status = 'pending'");
        mysqli_stmt_bind_param($activeBids, 'i', $id);
        mysqli_stmt_execute($activeBids);
        $cnt = mysqli_fetch_assoc(mysqli_stmt_get_result($activeBids))['cnt'];
        if ($cnt > 0) {
            echo json_encode(["success" => false, "message" => "Artikel kan niet verwijderd worden zolang er actieve biedingen lopen"]);
            return;
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Artikel succesvol verwijderd"]);
}
