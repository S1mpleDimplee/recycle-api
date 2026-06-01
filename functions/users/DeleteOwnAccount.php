<?php

function DeleteOwnAccount($data, $conn)
{
    $userId   = (int)($data['userid']   ?? 0);
    $password =       $data['password'] ?? '';

    if (!$userId || empty($password)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID en wachtwoord zijn verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT password, credit_id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(["success" => false, "message" => "Wachtwoord is onjuist"]);
        return;
    }

    // Block deletion when user still has active listings
    $activeProd = mysqli_prepare($conn,
        "SELECT COUNT(*) AS cnt FROM products WHERE user_id = ? AND product_availability = 'available'");
    mysqli_stmt_bind_param($activeProd, 'i', $userId);
    mysqli_stmt_execute($activeProd);
    $cnt = mysqli_fetch_assoc(mysqli_stmt_get_result($activeProd))['cnt'];
    if ($cnt > 0) {
        echo json_encode(["success" => false, "message" => "Deactiveer eerst al je actieve artikelen ({$cnt}) voordat je je account verwijdert"]);
        return;
    }

    $cancelBids = mysqli_prepare($conn,
        "UPDATE bids SET status = 'cancelled' WHERE bidder_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($cancelBids, 'i', $userId);
    mysqli_stmt_execute($cancelBids);

    $markBidsDeleted = mysqli_prepare($conn,
        "UPDATE bids SET status = 'seller_deleted'
         WHERE status = 'pending'
           AND product_id IN (SELECT id FROM products WHERE user_id = ?)");
    mysqli_stmt_bind_param($markBidsDeleted, 'i', $userId);
    mysqli_stmt_execute($markBidsDeleted);

    $hideProducts = mysqli_prepare($conn,
        "UPDATE products SET product_availability = 'deleted' WHERE user_id = ?");
    mysqli_stmt_bind_param($hideProducts, 'i', $userId);
    mysqli_stmt_execute($hideProducts);

    $del = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($del, 'i', $userId);
    mysqli_stmt_execute($del);

    if (!empty($user['credit_id'])) {
        $delCredit = mysqli_prepare($conn, "DELETE FROM credits WHERE id = ?");
        mysqli_stmt_bind_param($delCredit, 'i', $user['credit_id']);
        mysqli_stmt_execute($delCredit);
    }

    echo json_encode(["success" => true, "message" => "Account verwijderd"]);
}
