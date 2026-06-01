<?php

function DeleteProduct($data, $conn)
{
    $id          = $data['id']     ?? '';
    $requesterId = $data['userid'] ?? '';

    if (empty($id) || empty($requesterId)) {
        echo json_encode(["success" => false, "message" => "Artikel ID en gebruiker ID zijn verplicht"]);
        return;
    }

    // Fetch product owner
    $check = mysqli_prepare($conn, "SELECT user_id FROM products p WHERE id = ?");
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $checkResult = mysqli_stmt_get_result($check);
    $product = mysqli_fetch_assoc($checkResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    // Allow owner or admin
    if ($product['user_id'] != $requesterId && !isAdmin($requesterId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Artikel succesvol verwijderd"]);
}
