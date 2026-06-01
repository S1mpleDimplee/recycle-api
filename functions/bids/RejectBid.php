<?php

// Seller rejects a single bid (product stays available)
function RejectBid($data, $conn)
{
    $userId = $data['userid'] ?? '';
    $bidId  = $data['bid_id'] ?? '';

    if (empty($userId) || empty($bidId)) {
        echo json_encode(["success" => false, "message" => "userid en bid_id zijn verplicht"]);
        return;
    }

    // Load bid + product owner
    $stmt = mysqli_prepare($conn,
        "SELECT b.status, p.user_id AS owner_id
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
        echo json_encode(["success" => false, "message" => "Alleen openstaande boden kunnen worden afgewezen"]);
        return;
    }

    $update = mysqli_prepare($conn, "UPDATE bid SET status = 'rejected' WHERE id = ?");
    mysqli_stmt_bind_param($update, 'i', $bidId);
    mysqli_stmt_execute($update);

    echo json_encode(["success" => true, "message" => "Bod afgewezen"]);
}
