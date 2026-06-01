<?php

function PlaceBid($data, $conn)
{
    $bidderId  = $data['userid']     ?? '';
    $productId = $data['product_id'] ?? '';
    $amount    = $data['amount']     ?? '';

    if (empty($bidderId) || empty($productId) || $amount === '') {
        echo json_encode(["success" => false, "message" => "userid, product_id en amount zijn verplicht"]);
        return;
    }

    if ((int)$amount <= 0) {
        echo json_encode(["success" => false, "message" => "Bod moet groter dan 0 zijn"]);
        return;
    }

    // Check product exists, is available, and doesn't belong to the bidder
    $prodStmt = mysqli_prepare($conn, "SELECT user_id, product_availability FROM p WHERE id = ?");
    mysqli_stmt_bind_param($prodStmt, 'i', $productId);
    mysqli_stmt_execute($prodStmt);
    $prodResult = mysqli_stmt_get_result($prodStmt);
    $product = mysqli_fetch_assoc($prodResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }
    if ($product['user_id'] == $bidderId) {
        echo json_encode(["success" => false, "message" => "Je kunt niet bieden op je eigen artikel"]);
        return;
    }
    if ($product['product_availability'] !== 'available') {
        echo json_encode(["success" => false, "message" => "Dit artikel is niet meer beschikbaar"]);
        return;
    }

    // One pending bid per user per product
    $dupStmt = mysqli_prepare($conn,
        "SELECT id FROM bid WHERE product_id = ? AND bidder_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($dupStmt, 'ii', $productId, $bidderId);
    mysqli_stmt_execute($dupStmt);
    $dupResult = mysqli_stmt_get_result($dupStmt);
    if (mysqli_fetch_assoc($dupResult)) {
        echo json_encode(["success" => false, "message" => "Je hebt al een openstaand bod op dit artikel. Annuleer het eerst om opnieuw te bieden."]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO bid (product_id, bidder_id, amount) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iii', $productId, $bidderId, $amount);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij plaatsen bod: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Bod succesvol geplaatst",
        "data"    => ["bid_id" => mysqli_insert_id($conn)]
    ]);
}
