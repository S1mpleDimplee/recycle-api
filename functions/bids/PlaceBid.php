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

    // Auto-finalize if deadline already passed
    tryFinalizeAuction($productId, $conn);

    // Load product
    $prodStmt = mysqli_prepare($conn,
        "SELECT user_id, product_availability, bid_deadline, listing_type FROM products WHERE id = ?");
    mysqli_stmt_bind_param($prodStmt, 'i', $productId);
    mysqli_stmt_execute($prodStmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($prodStmt));

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }
    if ($product['user_id'] == $bidderId) {
        echo json_encode(["success" => false, "message" => "Je kunt niet bieden op je eigen artikel"]);
        return;
    }
    if (($product['listing_type'] ?? 'bid') !== 'bid') {
        echo json_encode(["success" => false, "message" => "Op dit artikel kan niet worden geboden"]);
        return;
    }
    if ($product['product_availability'] !== 'available') {
        echo json_encode(["success" => false, "message" => "Dit artikel is niet meer beschikbaar"]);
        return;
    }
    if (!empty($product['bid_deadline']) && strtotime($product['bid_deadline']) < time()) {
        echo json_encode(["success" => false, "message" => "De biedingstermijn voor dit artikel is verlopen"]);
        return;
    }

    // Check buyer has at least 1 Recy
    $creditId = ensureCreditRecord($bidderId, $conn);
    $balStmt  = mysqli_prepare($conn, "SELECT amount FROM credits WHERE id = ?");
    mysqli_stmt_bind_param($balStmt, 'i', $creditId);
    mysqli_stmt_execute($balStmt);
    $balRow = mysqli_fetch_assoc(mysqli_stmt_get_result($balStmt));
    if (!$balRow || (int)$balRow['amount'] < (int)$amount) {
        echo json_encode(["success" => false, "message" => "Je hebt onvoldoende Recy's om dit bod te plaatsen"]);
        return;
    }

    // Get current highest bid on this product
    $highStmt = mysqli_prepare($conn,
        "SELECT MAX(amount) AS max_bid FROM bids WHERE product_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($highStmt, 'i', $productId);
    mysqli_stmt_execute($highStmt);
    $highRow = mysqli_fetch_assoc(mysqli_stmt_get_result($highStmt));
    $currentHighest = (int)($highRow['max_bid'] ?? 0);

    if ((int)$amount <= $currentHighest) {
        echo json_encode(["success" => false, "message" => "Je bod moet hoger zijn dan het huidige hoogste bod ({$currentHighest} Recy's)"]);
        return;
    }

    // Cancel user's existing pending bid on this product (they are outbidding themselves or updating)
    $cancel = mysqli_prepare($conn,
        "UPDATE bids SET status = 'cancelled' WHERE product_id = ? AND bidder_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($cancel, 'ii', $productId, $bidderId);
    mysqli_stmt_execute($cancel);

    // Place new bid
    $stmt = mysqli_prepare($conn,
        "INSERT INTO bids (product_id, bidder_id, amount) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iii', $productId, $bidderId, $amount);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij plaatsen bod: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Bod succesvol geplaatst",
        "data"    => [
            "bid_id"          => mysqli_insert_id($conn),
            "highest_bid"     => (int)$amount,
        ]
    ]);
}
