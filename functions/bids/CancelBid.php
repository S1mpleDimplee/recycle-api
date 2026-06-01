<?php

function CancelBid($data, $conn)
{
    $userId = $data['userid'] ?? '';
    $bidId  = $data['bid_id'] ?? '';

    if (empty($userId) || empty($bidId)) {
        echo json_encode(["success" => false, "message" => "userid en bid_id zijn verplicht"]);
        return;
    }

    // Verify this bid belongs to the user and is still pending
    $stmt = mysqli_prepare($conn, "SELECT bidder_id, status FROM bid WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $bidId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bid = mysqli_fetch_assoc($result);

    if (!$bid) {
        echo json_encode(["success" => false, "message" => "Bod niet gevonden"]);
        return;
    }
    if ($bid['bidder_id'] != $userId) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }
    if ($bid['status'] !== 'pending') {
        echo json_encode(["success" => false, "message" => "Alleen openstaande boden kunnen worden geannuleerd"]);
        return;
    }

    $update = mysqli_prepare($conn, "UPDATE bid SET status = 'cancelled' WHERE id = ?");
    mysqli_stmt_bind_param($update, 'i', $bidId);
    mysqli_stmt_execute($update);

    echo json_encode(["success" => true, "message" => "Bod geannuleerd"]);
}
