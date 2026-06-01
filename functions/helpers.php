<?php

function serveBase($script)
{
    $host = 'http://' . $_SERVER['HTTP_HOST'];
    $longPath = '/phpopdrachten/derde_jaar/recycle-api/' . $script;
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $longPath)) {
        return $host . $longPath . '?id=';
    }
    return $host . '/recycle-api/' . $script . '?id=';
}

function serveImageUrl($productId, $pos = 1)
{
    $host     = 'http://' . $_SERVER['HTTP_HOST'];
    $longPath = '/phpopdrachten/derde_jaar/recycle-api/serve_image.php';
    $base     = file_exists($_SERVER['DOCUMENT_ROOT'] . $longPath)
        ? $host . $longPath
        : $host . '/recycle-api/serve_image.php';
    return $base . '?id=' . (int)$productId . '&pos=' . (int)$pos;
}

function getProductImages($productId, $conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT position FROM product_images WHERE product_id = ? ORDER BY position ASC");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $urls = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $urls[] = serveImageUrl($productId, $row['position']);
    }

    if (empty($urls)) {
        // Fallback: check legacy product_img
        $chk = mysqli_prepare($conn,
            "SELECT (product_img IS NOT NULL AND LENGTH(product_img) > 0) AS has_img FROM products WHERE id = ?");
        mysqli_stmt_bind_param($chk, 'i', $productId);
        mysqli_stmt_execute($chk);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
        if ($row && $row['has_img']) {
            $urls[] = serveImageUrl($productId, 1);
        }
    }

    return $urls;
}

function tryFinalizeAuction($productId, $conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT user_id, listing_type, bid_deadline, product_availability FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$product
        || $product['listing_type'] !== 'bid'
        || $product['product_availability'] !== 'available'
        || empty($product['bid_deadline'])
        || strtotime($product['bid_deadline']) > time()
    ) return false;

    // Find highest pending bid
    $bidStmt = mysqli_prepare($conn,
        "SELECT id, bidder_id, amount FROM bids
         WHERE product_id = ? AND status = 'pending'
         ORDER BY amount DESC LIMIT 1");
    mysqli_stmt_bind_param($bidStmt, 'i', $productId);
    mysqli_stmt_execute($bidStmt);
    $topBid = mysqli_fetch_assoc(mysqli_stmt_get_result($bidStmt));

    if (!$topBid) return false;

    $bidId   = $topBid['id'];
    $buyerId = $topBid['bidder_id'];
    $amount  = (int)$topBid['amount'];
    $sellerId = (int)$product['user_id'];

    $transfer = transferCredits($buyerId, $sellerId, $amount, $conn);
    if (!$transfer['ok']) return false;

    $q1 = mysqli_prepare($conn, "UPDATE bids SET status = 'accepted' WHERE id = ?");
    mysqli_stmt_bind_param($q1, 'i', $bidId);
    mysqli_stmt_execute($q1);

    $q2 = mysqli_prepare($conn,
        "UPDATE bids SET status = 'rejected' WHERE product_id = ? AND id != ? AND status = 'pending'");
    mysqli_stmt_bind_param($q2, 'ii', $productId, $bidId);
    mysqli_stmt_execute($q2);

    $q3 = mysqli_prepare($conn, "UPDATE products SET product_availability = 'sold' WHERE id = ?");
    mysqli_stmt_bind_param($q3, 'i', $productId);
    mysqli_stmt_execute($q3);

    $q4 = mysqli_prepare($conn,
        "INSERT INTO purchases (bid_id, product_id, buyer_id, seller_id, amount_paid) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($q4, 'iiiii', $bidId, $productId, $buyerId, $sellerId, $amount);
    mysqli_stmt_execute($q4);

    return true;
}

function isAdmin($userId, $conn)
{
    $stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user && $user['role'] === 'admin';
}

function requireAdmin($userId, $conn)
{
    if (!isAdmin($userId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        exit();
    }
}

/**
 * Returns the credit record id for a user.
 * Creates one (amount = 0) if the user has none yet.
 */
function ensureCreditRecord($userId, $conn)
{
    $stmt = mysqli_prepare($conn, "SELECT credit_id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) return null;

    if (!empty($row['credit_id'])) {
        return (int)$row['credit_id'];
    }

    // Create a new credit record and link it
    $ins = mysqli_prepare($conn, "INSERT INTO credits (amount) VALUES (0)");
    mysqli_stmt_execute($ins);
    $creditId = mysqli_insert_id($conn);

    $link = mysqli_prepare($conn, "UPDATE users SET credit_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($link, 'ii', $creditId, $userId);
    mysqli_stmt_execute($link);

    return $creditId;
}

/**
 * Transfers Recy credits from buyer to seller atomically.
 * Returns ['ok' => true] or ['ok' => false, 'message' => '...'].
 */
function transferCredits($buyerId, $sellerId, $amount, $conn)
{
    $buyerCreditId  = ensureCreditRecord($buyerId, $conn);
    $sellerCreditId = ensureCreditRecord($sellerId, $conn);

    if (!$buyerCreditId || !$sellerCreditId) {
        return ['ok' => false, 'message' => 'Credits record niet gevonden'];
    }

    // Check buyer balance
    $bal = mysqli_prepare($conn, "SELECT amount FROM credits WHERE id = ?");
    mysqli_stmt_bind_param($bal, 'i', $buyerCreditId);
    mysqli_stmt_execute($bal);
    $balResult = mysqli_stmt_get_result($bal);
    $balRow = mysqli_fetch_assoc($balResult);

    if (!$balRow || (int)$balRow['amount'] < $amount) {
        return ['ok' => false, 'message' => 'Koper heeft onvoldoende Recy\'s'];
    }

    // Deduct from buyer
    $deduct = mysqli_prepare($conn, "UPDATE credits SET amount = amount - ? WHERE id = ?");
    mysqli_stmt_bind_param($deduct, 'ii', $amount, $buyerCreditId);
    if (!mysqli_stmt_execute($deduct)) {
        return ['ok' => false, 'message' => 'Fout bij afschrijven credits'];
    }

    // Add to seller
    $add = mysqli_prepare($conn, "UPDATE credits SET amount = amount + ? WHERE id = ?");
    mysqli_stmt_bind_param($add, 'ii', $amount, $sellerCreditId);
    if (!mysqli_stmt_execute($add)) {
        return ['ok' => false, 'message' => 'Fout bij bijschrijven credits'];
    }

    return ['ok' => true];
}
