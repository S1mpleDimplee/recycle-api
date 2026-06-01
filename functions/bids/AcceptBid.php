<?php

function AcceptBid($data, $conn)
{
    $userId = $data['userid'] ?? '';
    $bidId  = $data['bid_id'] ?? '';

    if (empty($userId) || empty($bidId)) {
        echo json_encode(["success" => false, "message" => "userid en bid_id zijn verplicht"]);
        return;
    }

    // Load the bid + product + seller in one query
    $stmt = mysqli_prepare($conn,
        "SELECT b.status, b.amount, b.bidder_id,
                p.id AS product_id, p.user_id AS owner_id
         FROM bid b
         INNER JOIN p ON p.id = b.product_id
         WHERE b.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $bidId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo json_encode(["success" => false, "message" => "Bod niet gevonden"]);
        return;
    }
    if ($row['owner_id'] != $userId) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }
    if ($row['status'] !== 'pending') {
        echo json_encode(["success" => false, "message" => "Dit bod is niet meer openstaand"]);
        return;
    }

    $productId = $row['product_id'];
    $buyerId   = $row['bidder_id'];
    $sellerId  = (int)$userId;
    $amount    = (int)$row['amount'];

    // Transfer credits – checks buyer balance too
    $transfer = transferCredits($buyerId, $sellerId, $amount, $conn);
    if (!$transfer['ok']) {
        echo json_encode(["success" => false, "message" => $transfer['message']]);
        return;
    }

    // Accept this bid
    $accept = mysqli_prepare($conn, "UPDATE bid SET status = 'accepted' WHERE id = ?");
    mysqli_stmt_bind_param($accept, 'i', $bidId);
    mysqli_stmt_execute($accept);

    // Reject all other pending bids on this product
    $rejectOthers = mysqli_prepare($conn,
        "UPDATE bid SET status = 'rejected'
         WHERE product_id = ? AND id != ? AND status = 'pending'");
    mysqli_stmt_bind_param($rejectOthers, 'ii', $productId, $bidId);
    mysqli_stmt_execute($rejectOthers);

    // Mark product sold
    $sold = mysqli_prepare($conn, "UPDATE p SET product_availability = 'sold' WHERE id = ?");
    mysqli_stmt_bind_param($sold, 'i', $productId);
    mysqli_stmt_execute($sold);

    // Create purchase record
    $purchase = mysqli_prepare($conn,
        "INSERT INTO purchase (bid_id, product_id, buyer_id, seller_id, amount_paid)
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($purchase, 'iiiii', $bidId, $productId, $buyerId, $sellerId, $amount);
    mysqli_stmt_execute($purchase);

    echo json_encode([
        "success" => true,
        "message" => "Bod geaccepteerd – {$amount} Recy's overgemaakt naar jouw account"
    ]);
}
