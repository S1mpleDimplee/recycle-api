<?php

function GetUserDashboard($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    // User info + credit balance
    $userStmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.username, u.email, u.role, COALESCE(c.amount, 0) AS credits
         FROM users u
         LEFT JOIN credits c ON c.id = u.credit_id
         WHERE u.id = ?");
    mysqli_stmt_bind_param($userStmt, 'i', $userId);
    mysqli_stmt_execute($userStmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Gebruiker niet gevonden"]);
        return;
    }

    // Listing stats
    $listStmt = mysqli_prepare($conn,
        "SELECT
            COUNT(*)                                    AS total,
            SUM(product_availability = 'available')    AS available,
            SUM(product_availability = 'sold')         AS sold,
            SUM(product_availability = 'reserved')     AS reserved
         FROM products p WHERE user_id = ?");
    mysqli_stmt_bind_param($listStmt, 'i', $userId);
    mysqli_stmt_execute($listStmt);
    $listStats = mysqli_fetch_assoc(mysqli_stmt_get_result($listStmt));

    // Purchase stats (bought)
    $buyStmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total_bought, COALESCE(SUM(amount_paid), 0) AS total_spent
         FROM purchases WHERE buyer_id = ?");
    mysqli_stmt_bind_param($buyStmt, 'i', $userId);
    mysqli_stmt_execute($buyStmt);
    $buyStats = mysqli_fetch_assoc(mysqli_stmt_get_result($buyStmt));

    // Sales stats (sold)
    $sellStmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS total_sold, COALESCE(SUM(amount_paid), 0) AS total_earned
         FROM purchases WHERE seller_id = ?");
    mysqli_stmt_bind_param($sellStmt, 'i', $userId);
    mysqli_stmt_execute($sellStmt);
    $sellStats = mysqli_fetch_assoc(mysqli_stmt_get_result($sellStmt));

    // Pending bids placed by user
    $bidStmt = mysqli_prepare($conn,
        "SELECT COUNT(*) AS pending FROM bids WHERE bidder_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($bidStmt, 'i', $userId);
    mysqli_stmt_execute($bidStmt);
    $bidStats = mysqli_fetch_assoc(mysqli_stmt_get_result($bidStmt));

    // 5 most recent listings
    $recentListStmt = mysqli_prepare($conn,
        "SELECT id, product_name, product_price, product_availability
         FROM products p WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    mysqli_stmt_bind_param($recentListStmt, 'i', $userId);
    mysqli_stmt_execute($recentListStmt);
    $recentResult = mysqli_stmt_get_result($recentListStmt);
    $recentListings = [];
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentListings[] = $row;
    }

    // 5 most recent purchases
    $recentBuyStmt = mysqli_prepare($conn,
        "SELECT pur.id, pur.amount_paid, pur.created_at,
                p.product_name,
                seller.name AS seller_name
         FROM purchases pur
         INNER JOIN products p    ON p.id    = pur.product_id
         INNER JOIN users seller ON seller.id = pur.seller_id
         WHERE pur.buyer_id = ?
         ORDER BY pur.created_at DESC LIMIT 5");
    mysqli_stmt_bind_param($recentBuyStmt, 'i', $userId);
    mysqli_stmt_execute($recentBuyStmt);
    $recentBuyResult = mysqli_stmt_get_result($recentBuyStmt);
    $recentPurchases = [];
    while ($row = mysqli_fetch_assoc($recentBuyResult)) {
        $recentPurchases[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "user"    => $user,
            "listings" => [
                "total"     => (int)$listStats['total'],
                "available" => (int)$listStats['available'],
                "sold"      => (int)$listStats['sold'],
                "reserved"  => (int)$listStats['reserved'],
            ],
            "purchases" => [
                "total_bought" => (int)$buyStats['total_bought'],
                "total_spent"  => (int)$buyStats['total_spent'],
            ],
            "sales" => [
                "total_sold"   => (int)$sellStats['total_sold'],
                "total_earned" => (int)$sellStats['total_earned'],
            ],
            "bids_pending"    => (int)$bidStats['pending'],
            "recent_listings" => $recentListings,
            "recent_purchases" => $recentPurchases,
        ]
    ]);
}
