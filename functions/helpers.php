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
