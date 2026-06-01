<?php

function DeleteUser($data, $conn)
{
    $adminId = $data['adminid'] ?? '';
    $userId  = $data['userid']  ?? '';

    if (empty($adminId) || empty($userId)) {
        echo json_encode(["success" => false, "message" => "Admin ID en gebruiker ID zijn verplicht"]);
        return;
    }

    requireAdmin($adminId, $conn);

    // Delete user's listings first
    $delProducts = mysqli_prepare($conn, "DELETE FROM p WHERE user_id = ?");
    mysqli_stmt_bind_param($delProducts, 'i', $userId);
    mysqli_stmt_execute($delProducts);

    // Delete user's credit record
    $creditStmt = mysqli_prepare($conn, "SELECT credit_id FROM user WHERE id = ?");
    mysqli_stmt_bind_param($creditStmt, 'i', $userId);
    mysqli_stmt_execute($creditStmt);
    $creditResult = mysqli_stmt_get_result($creditStmt);
    $creditRow = mysqli_fetch_assoc($creditResult);

    $delUser = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
    mysqli_stmt_bind_param($delUser, 'i', $userId);
    $result = mysqli_stmt_execute($delUser);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . mysqli_error($conn)]);
        return;
    }

    if ($creditRow && !empty($creditRow['credit_id'])) {
        $delCredit = mysqli_prepare($conn, "DELETE FROM credit WHERE id = ?");
        mysqli_stmt_bind_param($delCredit, 'i', $creditRow['credit_id']);
        mysqli_stmt_execute($delCredit);
    }

    echo json_encode(["success" => true, "message" => "Gebruiker succesvol verwijderd"]);
}
