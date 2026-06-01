<?php

function GetAdminDashboard($data, $conn)
{
    $adminId = $data['adminid'] ?? '';

    if (empty($adminId)) {
        echo json_encode(["success" => false, "message" => "Admin ID is verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    // User counts
    $userRow   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user"));
    $adminRow  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role = 'admin'"));

    // Product counts
    $prod = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT
            COUNT(*) AS total,
            SUM(product_availability = 'available') AS available,
            SUM(product_availability = 'sold')      AS sold,
            SUM(product_availability = 'reserved')  AS reserved
         FROM p"));

    // Transaction stats
    $txRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS total_tx, COALESCE(SUM(amount_paid), 0) AS total_volume
         FROM purchase"));

    // Bid stats
    $bidRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT
            COUNT(*) AS total,
            SUM(status = 'pending')   AS pending,
            SUM(status = 'accepted')  AS accepted,
            SUM(status = 'rejected')  AS rejected,
            SUM(status = 'cancelled') AS cancelled
         FROM bid"));

    // 5 most recent users
    $recentUsersResult = mysqli_query($conn,
        "SELECT id, name, username, email, role, created_at FROM user ORDER BY id DESC LIMIT 5");
    $latestUsers = [];
    while ($row = mysqli_fetch_assoc($recentUsersResult)) {
        $latestUsers[] = $row;
    }

    // 5 most recent transactions
    $recentTxResult = mysqli_query($conn,
        "SELECT pur.id, pur.amount_paid, pur.created_at,
                p.product_name,
                buyer.name  AS buyer_name,
                seller.name AS seller_name
         FROM purchase pur
         INNER JOIN p    ON p.id    = pur.product_id
         INNER JOIN user buyer  ON buyer.id  = pur.buyer_id
         INNER JOIN user seller ON seller.id = pur.seller_id
         ORDER BY pur.created_at DESC LIMIT 5");
    $latestTransactions = [];
    while ($row = mysqli_fetch_assoc($recentTxResult)) {
        $latestTransactions[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "users" => [
                "total"  => (int)$userRow['total'],
                "admins" => (int)$adminRow['total'],
            ],
            "products" => [
                "total"     => (int)$prod['total'],
                "available" => (int)$prod['available'],
                "sold"      => (int)$prod['sold'],
                "reserved"  => (int)$prod['reserved'],
            ],
            "transactions" => [
                "total"        => (int)$txRow['total_tx'],
                "total_volume" => (int)$txRow['total_volume'],
            ],
            "bids" => [
                "total"     => (int)$bidRow['total'],
                "pending"   => (int)$bidRow['pending'],
                "accepted"  => (int)$bidRow['accepted'],
                "rejected"  => (int)$bidRow['rejected'],
                "cancelled" => (int)$bidRow['cancelled'],
            ],
            "latest_users"        => $latestUsers,
            "latest_transactions" => $latestTransactions,
        ]
    ]);
}
