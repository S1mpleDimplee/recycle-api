<?php

function BuyNow($data, $conn)
{
    $buyerId   = $data['userid']     ?? '';
    $productId = $data['product_id'] ?? '';

    if (empty($buyerId) || empty($productId)) {
        echo json_encode(["success" => false, "message" => "userid en product_id zijn verplicht"]);
        return;
    }

    // Load product
    $stmt = mysqli_prepare($conn,
        "SELECT id, user_id, product_price, product_availability, listing_type
         FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }
    if ($product['user_id'] == $buyerId) {
        echo json_encode(["success" => false, "message" => "Je kunt je eigen artikel niet kopen"]);
        return;
    }
    if (($product['listing_type'] ?? 'bid') !== 'buy') {
        echo json_encode(["success" => false, "message" => "Dit artikel is niet direct te kopen"]);
        return;
    }
    if ($product['product_availability'] !== 'available') {
        echo json_encode(["success" => false, "message" => "Dit artikel is niet meer beschikbaar"]);
        return;
    }

    $sellerId = (int)$product['user_id'];
    $amount   = (int)$product['product_price'];

    // Transfer credits
    $transfer = transferCredits($buyerId, $sellerId, $amount, $conn);
    if (!$transfer['ok']) {
        echo json_encode(["success" => false, "message" => $transfer['message']]);
        return;
    }

    // Mark product sold
    $sold = mysqli_prepare($conn, "UPDATE products SET product_availability = 'sold' WHERE id = ?");
    mysqli_stmt_bind_param($sold, 'i', $productId);
    mysqli_stmt_execute($sold);

    // Create purchase record (no bid_id for direct buy)
    $purchase = mysqli_prepare($conn,
        "INSERT INTO purchases (bid_id, product_id, buyer_id, seller_id, amount_paid)
         VALUES (NULL, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($purchase, 'iiii', $productId, $buyerId, $sellerId, $amount);
    mysqli_stmt_execute($purchase);

    echo json_encode([
        "success" => true,
        "message" => "Artikel gekocht voor {$amount} Recy's!",
    ]);
}
