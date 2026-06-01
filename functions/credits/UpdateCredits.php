<?php

// Adds (or subtracts with a negative value) credits for a user.
// Admin-only endpoint.
function UpdateCredits($data, $conn)
{
    $adminId = $data['adminid'] ?? '';
    $userId  = $data['userid']  ?? '';
    $delta   = $data['amount']  ?? null;

    if (empty($adminId) || empty($userId) || $delta === null) {
        echo json_encode(["success" => false, "message" => "adminid, userid en amount zijn verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    // Get the credit_id for this user
    $stmt = mysqli_prepare($conn, "SELECT credit_id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user || empty($user['credit_id'])) {
        echo json_encode(["success" => false, "message" => "Geen credits record gevonden voor deze gebruiker"]);
        return;
    }

    $creditId = $user['credit_id'];

    $update = mysqli_prepare($conn, "UPDATE credits SET amount = GREATEST(0, amount + ?) WHERE id = ?");
    mysqli_stmt_bind_param($update, 'ii', $delta, $creditId);
    $ok = mysqli_stmt_execute($update);

    if (!$ok) {
        echo json_encode(["success" => false, "message" => "Fout bij bijwerken credits: " . mysqli_error($conn)]);
        return;
    }

    // Return new balance
    $bal = mysqli_prepare($conn, "SELECT amount FROM credits WHERE id = ?");
    mysqli_stmt_bind_param($bal, 'i', $creditId);
    mysqli_stmt_execute($bal);
    $balResult = mysqli_stmt_get_result($bal);
    $balRow = mysqli_fetch_assoc($balResult);

    echo json_encode([
        "success" => true,
        "message" => "Credits bijgewerkt",
        "data"    => ["amount" => (int)$balRow['amount']]
    ]);
}
